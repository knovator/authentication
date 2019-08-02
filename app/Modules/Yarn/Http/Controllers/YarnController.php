<?php

namespace App\Modules\Yarn\Http\Controllers;

use App\Constants\GenerateNumber;
use App\Constants\Master as MasterConstant;
use App\Http\Controllers\Controller;
use App\Modules\Yarn\Http\Requests\CreateRequest;
use App\Modules\Yarn\Http\Requests\UpdateRequest;
use App\Modules\Yarn\Models\YarnOrder;
use App\Modules\Yarn\Repositories\YarnOrderRepository;
use App\Support\DestroyObject;
use App\Support\UniqueIdGenerator;
use DB;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Knovators\Masters\Repository\MasterRepository;
use Knovators\Support\Helpers\HTTPCode;
use Log;

/**
 * Class YarnController
 * @package App\Modules\Yarn\Http\Controllers
 */
class YarnController extends Controller
{

    use DestroyObject, UniqueIdGenerator;

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

            return $this->sendResponse($yarnOrder,
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
                'kg_qty'       => $purchasedThread->kg_qty,
                'status_id'    => $input['status_id'],
            ];
        }
        $yarnOrder->orderStocks()->createMany($stockItems);
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
            $this->storeThreadDetails($yarnOrder = $yarnOrder->fresh(), $input);
            DB::commit();
            $yarnOrder->load([
                'threads.threadColor.thread',
                'threads.threadColor.color',
                'customer',
                'status'
            ]);

            return $this->sendResponse($yarnOrder,
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
        $input['status_id'] = $this->masterRepository->findByCode(MasterConstant::SO_PENDING)->id;
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
                    __('messages.can_not_delete_order'),
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
        $yarnOrder->load([
            'threads.threadColor.thread:id,name,denier,company_name',
            'threads.threadColor.color:id,name,code',
            'customer.state:id,name,code,gst_code',
            'status:id,name,code'
        ]);

        return $this->sendResponse($yarnOrder,
            __('messages.retrieved', ['module' => 'Sales Order']),
            HTTPCode::OK);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request) {
        try {
            $orders = $this->yarnOrderRepository->getYarnOrderList($request->all());

            return $this->sendResponse($orders,
                __('messages.retrieved', ['module' => 'Sales Orders']),
                HTTPCode::OK);
        } catch (Exception $exception) {
            Log::error($exception);

            return $this->sendResponse(null, __('messages.something_wrong'),
                HTTPCode::UNPROCESSABLE_ENTITY);
        }
    }

}



