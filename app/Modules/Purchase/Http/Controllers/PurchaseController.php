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
use Knovators\Masters\Repository\MasterRepository;
use Knovators\Support\Helpers\HTTPCode;
use Knovators\Support\Traits\DestroyObject;
use Log;
use Str;

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
            DB::commit();

            return $this->sendResponse($purchaseOrder,
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
                __('messages.can_not_edit_purchase_order'),
                HTTPCode::UNPROCESSABLE_ENTITY);
        }
        $input = $request->all();

        try {
            DB::beginTransaction();
            $purchaseOrder->update($input);
            $this->storeThreadDetails($purchaseOrder, $input);
            DB::commit();

            return $this->sendResponse($purchaseOrder,
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
            Log::error('Unable to find status method: ' . $status);

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
        $input['status_id'] = $this->masterRepository->findByCode(MasterConstant::PO_PENDING)->id;

        return $this->updateStatus($purchaseOrder, $input);

    }


    /**
     * @param $purchaseOrder
     * @param $input
     * @return JsonResponse
     * @throws Exception
     */
    private function updateStatus(PurchaseOrder $purchaseOrder, $input) {
        try {
            $purchaseOrder->update($input);

            return $this->sendResponse($purchaseOrder->fresh(),
                __('messages.updated', ['module' => 'Status']),
                HTTPCode::OK);
        } catch (Exception $exception) {
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
        $purchaseOrder->load('threads');
        $input['status_id'] = $this->masterRepository->findByCode(MasterConstant::PO_DELIVERED)->id;
        $stockItems = [];
        foreach ($purchaseOrder->threads as $key => $purchasedThread) {
            $stockItems[$key] = [
                'product_id'   => $purchasedThread->thread_color_id,
                'product_type' => 'thread_color',
                'kg_qty'       => $purchasedThread->kg_qty,
                'status_id'    => $input['status_id'],
            ];
        }
        try {
            DB::beginTransaction();
            $purchaseOrder->orderStocks()->createMany($stockItems);
            $response = $this->updateStatus($purchaseOrder, $input);
            DB::commit();

            return $response;
        } catch (Exception $exception) {
            DB::rollBack();
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
    private function updatePOCANCELEDStatus(PurchaseOrder $purchaseOrder, $input) {
        $input['status_id'] = $this->masterRepository->findByCode(MasterConstant::PO_CANCELED)->id;

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


}



