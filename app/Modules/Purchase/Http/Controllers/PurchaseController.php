<?php

namespace App\Modules\Purchase\Http\Controllers;

use App\Constants\GenerateNumber;
use App\Constants\Master as MasterConstant;
use App\Http\Controllers\Controller;
use App\Modules\Purchase\Http\Requests\CreateRequest;
use App\Modules\Purchase\Http\Requests\StatusRequest;
use App\Modules\Purchase\Http\Requests\UpdateRequest;
use App\Modules\Purchase\Models\PurchaseOrder;
use App\Modules\Purchase\Repositories\PurchaseOrderRepository;
use App\Modules\Purchase\Http\Resources\PurchaseOrder as PurchaseOrderResource;
use App\Support\UniqueIdGenerator;
use DB;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Knovators\Masters\Repository\MasterRepository;
use Knovators\Support\Helpers\HTTPCode;
use App\Support\DestroyObject;
use Log;
use Maatwebsite\Excel\Facades\Excel;
use Str;
use App\Modules\Purchase\Http\Exports\PurchaseOrder as ExportPurchaseOrder;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Class PurchaseController
 * @package App\Modules\Purchase\Http\Controllers
 */
class PurchaseController extends Controller
{

    use DestroyObject, UniqueIdGenerator;

    protected $purchaseOrderRepository;

    protected $masterRepository;

    /**
     * PurchaseController constructor
     * @param PurchaseOrderRepository $purchaseOrderRepository
     * @param MasterRepository        $masterRepository
     */
    public function __construct(
        PurchaseOrderRepository $purchaseOrderRepository,
        MasterRepository $masterRepository
    ) {
        $this->purchaseOrderRepository = $purchaseOrderRepository;
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
            $input['order_no'] = $this->generateUniqueId(GenerateNumber::PURCHASE);
            $input['status_id'] = $this->masterRepository->findByCode(MasterConstant::PO_PENDING)->id;
            $purchaseOrder = $this->purchaseOrderRepository->create($input);
            /** @var PurchaseOrder $purchaseOrder */
            $purchaseOrder->threads()->createMany($input['threads']);
            $this->storeStockOrders($purchaseOrder, $input);
            DB::commit();
            $purchaseOrder->load([
                'threads.threadColor.thread',
                'threads.threadColor.color',
                'customer',
                'status'
            ]);

            return $this->sendResponse($this->makeResource($purchaseOrder),
                __('messages.created', ['module' => 'Purchase']),
                HTTPCode::CREATED);
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error($exception);

            return $this->sendResponse(null, __('messages.something_wrong'),
                HTTPCode::UNPROCESSABLE_ENTITY, $exception);
        }
    }


    /**
     * @param PurchaseOrder $purchaseOrder
     * @param UpdateRequest $request
     * @return mixed
     * @throws Exception
     */
    public function update(PurchaseOrder $purchaseOrder, UpdateRequest $request) {
        $purchaseOrder->load('status');
        if ($purchaseOrder->status->code === MasterConstant::PO_DELIVERED) {
            return $this->sendResponse(null,
                __('messages.can_not_edit_order'),
                HTTPCode::UNPROCESSABLE_ENTITY);
        }
        $input = $request->all();

        try {
            DB::beginTransaction();
            $purchaseOrder->update($input);
            $this->storeThreadDetails($purchaseOrder = $purchaseOrder->fresh(), $input);
            DB::commit();

            return $this->sendResponse($this->makeResource($purchaseOrder->load([
                'threads.threadColor.thread',
                'threads.threadColor.color',
                'customer',
                'status'
            ])),
                __('messages.updated', ['module' => 'Purchase']),
                HTTPCode::OK);
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error($exception);

            return $this->sendResponse(null, __('messages.something_wrong'),
                HTTPCode::UNPROCESSABLE_ENTITY, $exception);
        }
    }


    /**
     * @param Request $request
     * @return JsonResponse|BinaryFileResponse
     */
    public function exportCsv(Request $request) {
        try {
            $purchases = $this->purchaseOrderRepository->getPurchaseOrderList($request->all(),
                true);
            if (($purchases = collect($purchases->getData()->data))->isEmpty()) {
                return $this->sendResponse(null,
                    __('messages.can_not_export', ['module' => 'Purchase orders']),
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
        return Excel::download(new ExportPurchaseOrder($purchases),
            'orders.xlsx');
    }


    /**
     * @param $purchaseOrder
     * @param $input
     */
    private function storeThreadDetails(PurchaseOrder $purchaseOrder, $input) {
        $newItems = [];
        foreach ($input['threads'] as $threadDetail) {
            if (isset($threadDetail['id'])) {
                $purchaseOrder->threads()->whereId($threadDetail['id'])->update($threadDetail);
            } else {
                $newItems[] = $threadDetail;
            }

        }
        if (!empty($newItems)) {
            $purchaseOrder->threads()->createMany($newItems);
        }
        if (isset($input['removed_threads_id']) && !empty($input['removed_threads_id'])) {
            $purchaseOrder->threads()->whereIn('id', $input['removed_threads_id'])
                          ->delete();
        }
        $purchaseOrder->orderStocks()->delete();
        $input['status_id'] = $purchaseOrder->status_id;
        $this->storeStockOrders($purchaseOrder, $input);
    }


    /**
     * @param StatusRequest $request
     * @return JsonResponse
     */
    public function changeStatus(StatusRequest $request) {
        $status = $request->get('code');
        $method = 'update' . Str::studly($status) . 'Status';
        try {
            $purchaseOrder = $this->purchaseOrderRepository->find($request->get('purchase_order_id'));

            return $this->{$method}($purchaseOrder, $request->all());
        } catch (Exception $exception) {
            Log::error($exception);

            return $this->sendResponse(null, __('messages.something_wrong'),
                HTTPCode::UNPROCESSABLE_ENTITY);
        }

    }


    /**
     * @param PurchaseOrder $purchaseOrder
     * @param               $input
     * @return JsonResponse
     * @throws Exception
     */
    private function updatePOPENDINGStatus(PurchaseOrder $purchaseOrder, $input) {
        return $this->updateStatus($purchaseOrder, $input);

    }


    /**
     * @param $purchaseOrder
     * @param $input
     * @return JsonResponse
     * @throws Exception
     */
    private function updateStatus(PurchaseOrder $purchaseOrder, $input) {
        $input['status_id'] = $this->masterRepository->findByCode($input['code'])->id;
        try {
            DB::beginTransaction();
            $purchaseOrder->update($input);
            $purchaseOrder->orderStocks()->update(['status_id' => $input['status_id']]);
            DB::commit();

            return $this->sendResponse($this->makeResource($purchaseOrder->fresh([
                'threads.threadColor.thread',
                'threads.threadColor.color',
                'customer',
                'status'
            ])),
                __('messages.updated', ['module' => 'Status']),
                HTTPCode::OK);
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error($exception);
            throw $exception;
        }
    }

    /**
     * @param PurchaseOrder $purchaseOrder
     * @param               $input
     * @return JsonResponse
     * @throws Exception
     */
    private function updatePODELIVEREDStatus(PurchaseOrder $purchaseOrder, $input) {

        if (!isset($input['challan_no'])) {
            return $this->sendResponse(null, 'Challan Number is must be required',
                HTTPCode::UNPROCESSABLE_ENTITY);
        }
        try {
            return $this->updateStatus($purchaseOrder, $input);
        } catch (Exception $exception) {
            Log::error($exception);

            return $this->sendResponse(null, __('messages.something_wrong'),
                HTTPCode::UNPROCESSABLE_ENTITY);
        }

    }


    /**
     * @param $purchaseOrder
     * @param $input
     */
    private function storeStockOrders(PurchaseOrder $purchaseOrder, $input) {
        $stockItems = [];
        $purchaseOrder->load('threads');
        foreach ($purchaseOrder->threads as $key => $purchasedThread) {
            $stockItems[$key] = [
                'product_id'   => $purchasedThread->thread_color_id,
                'product_type' => 'thread_color',
                'kg_qty'       => $purchasedThread->kg_qty,
                'status_id'    => $input['status_id'],
                'purchased_thread_id'    => $purchasedThread->id,
            ];
        }
        $purchaseOrder->orderStocks()->createMany($stockItems);
    }

    /**
     * @param PurchaseOrder $purchaseOrder
     * @param               $input
     * @return JsonResponse
     * @throws Exception
     */
    private function updatePOCANCELEDStatus(PurchaseOrder $purchaseOrder, $input) {
        return $this->updateStatus($purchaseOrder, $input);
    }

    /**
     * @param PurchaseOrder $purchaseOrder
     * @return JsonResponse
     */
    public function show(PurchaseOrder $purchaseOrder) {
        $purchaseOrder->load([
            'threads.threadColor.thread',
            'threads.threadColor.color',
            'customer',
            'status'
        ]);

        return $this->sendResponse($this->makeResource($purchaseOrder),
            __('messages.retrieved', ['module' => 'Purchase Order']),
            HTTPCode::OK);
    }


    /**
     * @param $purchaseOrder
     * @return PurchaseOrderResource
     */
    private function makeResource($purchaseOrder) {
        return new PurchaseOrderResource($purchaseOrder);
    }


    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request) {
        try {
            $orders = $this->purchaseOrderRepository->getPurchaseOrderList($request->all());

            return $this->sendResponse($orders,
                __('messages.retrieved', ['module' => 'Orders']),
                HTTPCode::OK);
        } catch (Exception $exception) {
            Log::error($exception);

            return $this->sendResponse(null, __('messages.something_wrong'),
                HTTPCode::UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * @param PurchaseOrder $purchaseOrder
     * @return JsonResponse
     */
    public function destroy(PurchaseOrder $purchaseOrder) {
        try {
            $purchaseOrder->load('status');
            if ($purchaseOrder->status->code === MasterConstant::PO_DELIVERED) {
                return $this->sendResponse(null,
                    __('messages.can_not_delete_order'),
                    HTTPCode::UNPROCESSABLE_ENTITY);
            }

            return $this->destroyModelObject([], $purchaseOrder, 'Purchase Order');

        } catch (Exception $exception) {
            Log::error($exception);

            return $this->sendResponse(null, __('messages.something_wrong'),
                HTTPCode::UNPROCESSABLE_ENTITY, $exception);
        }
    }

}



