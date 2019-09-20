<?php

namespace App\Modules\Yarn\Http\Controllers;

use App\Constants\GenerateNumber;
use App\Constants\Master;
use App\Constants\Master as MasterConstant;
use App\Http\Controllers\Controller;
use App\Jobs\YarnOrderFormJob;
use App\Modules\Yarn\Exports\YarnOrder as ExportYarnOrder;
use App\Modules\Yarn\Http\Requests\CreateRequest;
use App\Modules\Yarn\Http\Requests\MailRequest;
use App\Modules\Yarn\Http\Requests\PaymentRequest;
use App\Modules\Yarn\Http\Requests\StatusRequest;
use App\Modules\Yarn\Http\Requests\UpdateRequest;
use App\Modules\Yarn\Models\YarnOrder;
use App\Modules\Yarn\Repositories\YarnOrderRepository;
use App\Modules\Yarn\Support\ExportYarnOrderSummary;
use App\Support\DestroyObject;
use App\Support\UniqueIdGenerator;
use DB;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Knovators\Masters\Repository\MasterRepository;
use Knovators\Support\Helpers\HTTPCode;
use Log;
use Maatwebsite\Excel\Facades\Excel;
use Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Class YarnController
 * @package App\Modules\Yarn\Http\Controllers
 */
class YarnController extends Controller
{

    use DestroyObject, UniqueIdGenerator, ExportYarnOrderSummary;

    protected $yarnOrderRepository;

    protected $masterRepository;

    /**
     * YarnController constructor
     * @param YarnOrderRepository $yarnOrderRepository
     * @param MasterRepository    $masterRepository
     */
    public function __construct(
        YarnOrderRepository $yarnOrderRepository,
        MasterRepository $masterRepository
    ) {
        $this->yarnOrderRepository = $yarnOrderRepository;
        $this->masterRepository = $masterRepository;
    }

    /**
     * @param CreateRequest $request
     * @return mixed
     * @throws Exception
     */
    public function store(CreateRequest $request) {
        $input = $request->all();
        try {
            DB::beginTransaction();
            $input['order_no'] = $this->generateUniqueId(GenerateNumber::YARN_SALES);
            $input['status_id'] = $this->masterRepository->findByCode(MasterConstant::SO_PENDING)->id;
            $yarnOrder = $this->yarnOrderRepository->create($input);
            /** @var YarnOrder $yarnOrder */
            $yarnOrder->threads()->createMany($input['threads']);
            $this->storeStockOrders($yarnOrder, $input);
            DB::commit();

            return $this->sendResponse($yarnOrder->load($this->commonRelations()),
                __('messages.created', ['module' => 'Sales Order']),
                HTTPCode::CREATED);
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error($exception);

            return $this->sendResponse(null, __('messages.something_wrong'),
                HTTPCode::UNPROCESSABLE_ENTITY, $exception);
        }
    }

    /**
     * @param $yarnOrder
     * @param $input
     */
    private function storeStockOrders(YarnOrder $yarnOrder, $input) {
        $stockItems = [];
        $yarnOrder->load('threads');
        foreach ($yarnOrder->threads as $key => $purchasedThread) {
            $stockItems[$key] = [
                'product_id'   => $purchasedThread->thread_color_id,
                'product_type' => 'thread_color',
                'kg_qty'       => -1 * $purchasedThread->kg_qty,
                'status_id'    => $input['status_id'],
            ];
        }
        $yarnOrder->orderStocks()->createMany($stockItems);
    }

    /**
     * @return array
     */
    private function commonRelations() {
        return [
            'threads.threadColor.thread:id,name,denier,company_name',
            'threads.threadColor.color:id,name,code',
            'customer.state:id,name,code,gst_code',
            'status:id,name,code',
            'manufacturingCompany:id,name'
        ];
    }

    /**
     * @param YarnOrder     $yarnOrder
     * @param UpdateRequest $request
     * @return mixed
     * @throws Exception
     */
    public function update(YarnOrder $yarnOrder, UpdateRequest $request) {
        $yarnOrder->load('status');
        if ($yarnOrder->status->code === MasterConstant::SO_DELIVERED) {
            return $this->sendResponse(null,
                __('messages.can_not_edit_order'),
                HTTPCode::UNPROCESSABLE_ENTITY);
        }
        $input = $request->all();
        try {
            DB::beginTransaction();
            $yarnOrder->update($input);
            $this->storeThreadDetails($yarnOrder->refresh(), $input);
            DB::commit();

            return $this->sendResponse($yarnOrder->load($this->commonRelations()),
                __('messages.updated', ['module' => 'Sales Order']),
                HTTPCode::OK);
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error($exception);

            return $this->sendResponse(null, __('messages.something_wrong'),
                HTTPCode::UNPROCESSABLE_ENTITY, $exception);
        }
    }

    /**
     * @param $yarnOrder
     * @param $input
     */
    private function storeThreadDetails(YarnOrder $yarnOrder, $input) {
        $newItems = [];
        foreach ($input['threads'] as $threadDetail) {
            if (isset($threadDetail['id'])) {
                $yarnOrder->threads()->whereId($threadDetail['id'])->update($threadDetail);
            } else {
                $newItems[] = $threadDetail;
            }

        }
        if (!empty($newItems)) {
            $yarnOrder->threads()->createMany($newItems);
        }
        if (isset($input['removed_threads_id']) && !empty($input['removed_threads_id'])) {
            $yarnOrder->threads()->whereIn('id', $input['removed_threads_id'])
                      ->delete();
        }
        $yarnOrder->orderStocks()->delete();
        $input['status_id'] = $yarnOrder->status_id;
        $this->storeStockOrders($yarnOrder, $input);
    }

    /**
     * @param YarnOrder $yarnOrder
     * @return JsonResponse
     */
    public function destroy(YarnOrder $yarnOrder) {
        try {
            $yarnOrder->load('status');
            if ($yarnOrder->status->code === MasterConstant::SO_DELIVERED) {
                return $this->sendResponse(null,
                    __('messages.can_not_delete_complete_order'),
                    HTTPCode::UNPROCESSABLE_ENTITY);
            }

            return $this->destroyModelObject([], $yarnOrder, 'Sales Order');

        } catch (Exception $exception) {
            Log::error($exception);

            return $this->sendResponse(null, __('messages.something_wrong'),
                HTTPCode::UNPROCESSABLE_ENTITY, $exception);
        }
    }

    /**
     * @param YarnOrder $yarnOrder
     * @return JsonResponse
     */
    public function show(YarnOrder $yarnOrder) {
        return $this->sendResponse($yarnOrder->load($this->commonRelations()),
            __('messages.retrieved', ['module' => 'Sales Order']),
            HTTPCode::OK);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request) {
        $input = $request->all();
        if ($request->has('payment') && $request->get('payment') == 'no') {
            $input['delivered_id'] = $this->masterRepository->findByCode(MasterConstant::SO_DELIVERED)->id;
        }
        try {
            $orders = $this->yarnOrderRepository->getYarnOrderList($input,
                $this->commonRelations());

            return $this->sendResponse($orders,
                __('messages.retrieved', ['module' => 'Sales Orders']),
                HTTPCode::OK);
        } catch (Exception $exception) {
            Log::error($exception);

            return $this->sendResponse(null, __('messages.something_wrong'),
                HTTPCode::UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * @param StatusRequest $request
     * @return JsonResponse
     */
    public function changeStatus(StatusRequest $request) {
        $status = $request->get('code');
        $method = 'update' . Str::studly($status) . 'Status';
        try {
            $yarnOrder = $this->yarnOrderRepository->find($request->get('yarn_order_id'));

            return $this->{$method}($yarnOrder, $request->all());
        } catch (Exception $exception) {
            Log::error($exception);

            return $this->sendResponse(null, __('messages.something_wrong'),
                HTTPCode::UNPROCESSABLE_ENTITY);
        }

    }

    /**
     * @return JsonResponse
     */
    public function statuses() {
        $statuses = $this->masterRepository->with([
            'childMasters' => function ($childMasters) {
                /** @var Builder $childMasters */
                $childMasters->whereIn('code', [
                    MasterConstant::SO_MANUFACTURING,
                    MasterConstant::SO_COMPLETED
                ])->select([
                    'id',
                    'name',
                    'code',
                    'parent_id'
                ]);
            }
        ])->findByCode(MasterConstant::SALES_STATUS);

        return $this->sendResponse($statuses->childMasters,
            __('messages.retrieved', ['module' => 'Statuses']),
            HTTPCode::OK);
    }

    /**
     * @param Request $request
     * @return JsonResponse|BinaryFileResponse
     */
    public function exportCsv(Request $request) {
        $input = $request->all();
        if ($request->has('payment') && $request->get('payment') == 'no') {
            $input['delivered_id'] = $this->masterRepository->findByCode(MasterConstant::SO_DELIVERED)->id;
        }
        try {
            $purchases = $this->yarnOrderRepository->getYarnOrderList($input,
                $this->commonRelations(), true);
            if (($purchases = collect($purchases->getData()->data))->isEmpty()) {
                return $this->sendResponse(null,
                    __('messages.can_not_export', ['module' => 'Sales orders']),
                    HTTPCode::OK);
            }

            return $this->downloadCsv($purchases);
        } catch (Exception $exception) {
            Log::error($exception);

            return $this->sendResponse(null, __('messages.something_wrong'),
                HTTPCode::UNPROCESSABLE_ENTITY, $exception);
        }
    }

    /**
     * @param $purchases
     * @return BinaryFileResponse
     */
    private function downloadCsv($purchases) {
        return Excel::download(new ExportYarnOrder($purchases),
            'orders.xlsx');
    }

    /**
     * @param YarnOrder      $yarnOrder
     * @param PaymentRequest $request
     * @return JsonResponse
     */
    public function updatePayment(YarnOrder $yarnOrder, PaymentRequest $request) {
        $yarnOrder->update($request->all());

        return $this->sendResponse($yarnOrder->fresh($this->commonRelations()),
            __('messages.updated', ['module' => 'Yarn']),
            HTTPCode::OK);
    }

    /**
     * @param YarnOrder $yarnOrder
     * @return Response
     */
    public function exportSummary(YarnOrder $yarnOrder) {
        return $this->renderSummary($yarnOrder)
                    ->download($yarnOrder->order_no . ".pdf");
    }

    /**
     * @param YarnOrder   $yarnOrder
     * @param MailRequest $request
     * @return JsonResponse
     */
    public function sendMailToCustomer(YarnOrder $yarnOrder, MailRequest $request) {
        $input = $request->all();
        $yarnOrder->load('customer');
        try {
            if (is_null($yarnOrder->customer->email)) {
                $yarnOrder->customer()->update(['email' => $input['email']]);
                $yarnOrder = $yarnOrder->fresh();
            }
            YarnOrderFormJob::dispatch($yarnOrder)->delay(now()->addSeconds(10));

            return $this->sendResponse($yarnOrder,
                __('messages.created', ['module' => 'Mail']),
                HTTPCode::OK);
        } catch (Exception $exception) {
            Log::error($exception);

            return $this->sendResponse(null, __('messages.something_wrong'),
                HTTPCode::UNPROCESSABLE_ENTITY, $exception);
        }

    }

    /**
     * @param YarnOrder     $yarnOrder
     * @param               $input
     * @return JsonResponse
     * @throws Exception
     */
    private function updateSOPENDINGStatus(YarnOrder $yarnOrder, $input) {
        return $this->updateStatus($yarnOrder, $input);

    }

    /**
     * @param $yarnOrder
     * @param $input
     * @return JsonResponse
     * @throws Exception
     */
    private function updateStatus(YarnOrder $yarnOrder, $input) {
        $input['status_id'] = $this->masterRepository->findByCode($input['code'])->id;
        try {
            DB::beginTransaction();
            $yarnOrder->update($input);
            $yarnOrder->orderStocks()->update(['status_id' => $input['status_id']]);
            DB::commit();

            return $this->sendResponse($yarnOrder->fresh($this->commonRelations()),
                __('messages.updated', ['module' => 'Status']),
                HTTPCode::OK);
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error($exception);
            throw $exception;
        }
    }

    /**
     * @param YarnOrder     $yarnOrder
     * @param               $input
     * @return JsonResponse
     * @throws Exception
     */
    private function updateSOCANCELEDStatus(YarnOrder $yarnOrder, $input) {
        return $this->updateStatus($yarnOrder, $input);
    }

    /**
     * @param YarnOrder     $yarnOrder
     * @param               $input
     * @return JsonResponse
     * @throws Exception
     */
    private function updateSODELIVEREDStatus(YarnOrder $yarnOrder, $input) {

        $yarnOrder->load([
            'threads.threadColor' => function ($threadColor) {
                /** @var Builder $threadColor */
                $threadColor->with(['thread', 'color', 'availableStock']);
            }
        ]);
        foreach ($yarnOrder->threads as $yarnThread) {
            if (!is_null($yarnThread->threadColor->availableStock)) {
                $newQty = $yarnThread->threadColor->availableStock->available_qty -
                    $yarnThread->kg_qty;
            } else {
                $newQty = -1 * (int) $yarnThread->kg_qty;
            }
            if ($newQty < 0) {
                $newQty = ceil(str_replace('-', '', $newQty));

                return $this->sendResponse(null,
                    "You need to purchase {$yarnThread->threadColor->thread->denier}-{$yarnThread->threadColor->thread->name}-{$yarnThread->threadColor->color->name} ({$newQty} KG) for deliver this order.",
                    HTTPCode::UNPROCESSABLE_ENTITY);
            }

        }
        try {
            return $this->updateStatus($yarnOrder, $input);
        } catch (Exception $exception) {
            Log::error($exception);

            return $this->sendResponse(null, __('messages.something_wrong'),
                HTTPCode::UNPROCESSABLE_ENTITY);
        }

    }

}



