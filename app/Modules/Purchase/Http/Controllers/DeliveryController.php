<?php

namespace App\Modules\Purchase\Http\Controllers;

use App\Constants\GenerateNumber;
use App\Constants\Master as MasterConstant;
use App\Http\Controllers\Controller;
use App\Modules\Purchase\Http\Requests\Delivery\CreateRequest;
use App\Modules\Purchase\Models\PurchaseOrder;
use App\Modules\Purchase\Models\PurchaseOrderThread;
use App\Modules\Purchase\Models\PurchasePartialOrder;
use App\Modules\Purchase\Repositories\DeliveryRepository;
use App\Modules\Purchase\Repositories\PurchasedThreadRepository;
use App\Modules\Purchase\Models\PurchaseDelivery;
use App\Modules\Stock\Repositories\StockRepository;
use App\Modules\Thread\Constants\ThreadType;
use App\Repositories\MasterRepository;
use App\Support\Formula;
use App\Support\UniqueIdGenerator;
use DB;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Knovators\Support\Helpers\HTTPCode;
use Log;

/**
 * Class DeliveryController
 * @package App\Modules\Purchase\Http\Controllers
 */
class DeliveryController extends Controller
{

    use UniqueIdGenerator;

    protected $masterRepository;

    protected $stockRepository;

    protected $deliveryRepository;

    protected $purchasedThreadRepository;

    /**
     * DeliveryController constructor.
     * @param MasterRepository          $masterRepository
     * @param StockRepository           $stockRepository
     * @param DeliveryRepository        $deliveryRepository
     * @param PurchasedThreadRepository $purchasedThreadRepository
     */
    public function __construct(
        MasterRepository $masterRepository,
        StockRepository $stockRepository,
        DeliveryRepository $deliveryRepository,
        PurchasedThreadRepository $purchasedThreadRepository
    ) {
        $this->masterRepository = $masterRepository;
        $this->stockRepository = $stockRepository;
        $this->deliveryRepository = $deliveryRepository;
        $this->purchasedThreadRepository = $purchasedThreadRepository;
    }


    /**
     * @param PurchaseOrder $purchaseOrder
     * @param CreateRequest $request
     * @return mixed
     * @throws Exception
     */
    public function store(PurchaseOrder $purchaseOrder, CreateRequest $request) {
        $input = $request->all();
        $orders = collect($input['orders']);
        if ($this->checkQuantityNotExists($purchaseOrder, $orders)) {
            return $this->sendResponse(null, __('messages.quantity_not_exists'),
                HTTPCode::UNPROCESSABLE_ENTITY);
        }
        $input['status_id'] = $this->masterRepository->findByCode(MasterConstant::PO_DELIVERED)->id;
        $input['delivery_no'] = $this->generateUniqueId(GenerateNumber::PO_DELIVERY);
        $input['total_kg'] = $orders->sum('kg_qty');
        try {
            DB::beginTransaction();
            $delivery = $purchaseOrder->deliveries()->create($input);
            /** @var PurchaseDelivery $delivery */
            $delivery->partialOrders()->createMany($input['orders']);
            $this->storeStockDetails($purchaseOrder,
                $orders->pluck('purchase_order_thread_id')->toArray());
            DB::commit();

            return $this->sendResponse($delivery,
                __('messages.created', ['module' => 'Delivery']),
                HTTPCode::CREATED);
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error($exception);

            return $this->sendResponse(null, __('messages.something_wrong'),
                HTTPCode::UNPROCESSABLE_ENTITY, $exception);
        }
    }

    /**
     * @param      $purchaseOrder
     * @param      $orders
     * @param null $deliveryId
     * @return bool
     */
    private function checkQuantityNotExists($purchaseOrder, $orders, $deliveryId = null) {
        $orders = collect($orders)->groupBy('purchase_order_thread_id');
        $purchasedThreads = $this->purchasedThreadRepository->getOrderRecipeList($purchaseOrder->id,
            $deliveryId);
        foreach ($orders as $key => $order) {
            $orderThreads = $purchasedThreads->find($key);
            if ($orderThreads->remaining_kg_qty < $order->sum('kg_qty')) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param PurchaseOrder $purchaseOrder
     * @param array         $orderIds
     */
    private function storeStockDetails(
        PurchaseOrder $purchaseOrder,
        array $orderIds
    ) {
        $purchaseOrder->load([
            'threads' => function ($threads) use ($orderIds) {
                /** @var Builder $threads */
                $threads->whereKey($orderIds)->with(['partialOrders.delivery']);
            },
        ]);
        $purchaseOrder->orderStocks()->createMany($this->getStockQuantity($purchaseOrder));
    }

    /**
     * @param $purchaseOrder
     * @return array
     */
    private function getStockQuantity($purchaseOrder) {
        $stockQty = [];
        $pendingStatusId = $this->masterRepository->findByCode(MasterConstant::PO_PENDING)->id;
        foreach ($purchaseOrder->threads as $orderThread) {
            // create partial order stocks
            foreach ($orderThread->partialOrders as $partialOrder) {
                $stockQty[] = $this->setStockArray($orderThread,
                    $partialOrder->delivery->status_id, $partialOrder->kg_qty, $partialOrder);
            }
            /** @var PurchaseOrderThread $orderThread */
            $remainingKgQty = ($orderThread->kg_qty - $orderThread->partialOrders->sum('kg_qty'));
            // create remaining order stocks
            if ($remainingKgQty != 0) {
                $stockQty[] = $this->setStockArray($orderThread,
                    $pendingStatusId, $remainingKgQty);

            }

        }

        return $stockQty;
    }

    /**
     * @param                      $orderThread
     * @param                      $statusId
     * @param                      $kgQty
     * @param bool                 $partialOrder
     * @return array
     */
    private function setStockArray(
        $orderThread,
        $statusId,
        $kgQty,
        $partialOrder = false
    ) {
        $stock = [
            'purchased_thread_id' => $orderThread->id,
            'product_id'          => $orderThread->thread_color_id,
            'product_type'        => 'thread_color',
            'status_id'           => $statusId,
            'kg_qty'              => $kgQty,
        ];
        if ($partialOrder) {
            /** @var PurchasePartialOrder $partialOrder */
            $stock['partial_order_id'] = $partialOrder->id;
        }

        return $stock;
    }


}
