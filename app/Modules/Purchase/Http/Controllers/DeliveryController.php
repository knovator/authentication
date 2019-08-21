<?php

namespace App\Modules\Purchase\Http\Controllers;

use App\Constants\GenerateNumber;
use App\Constants\Master as MasterConstant;
use App\Http\Controllers\Controller;
use App\Modules\Purchase\Http\Requests\Delivery\CreateRequest;
use App\Modules\Purchase\Models\PurchaseOrder;
use App\Modules\Purchase\Models\PurchasePartialOrder;
use App\Modules\Purchase\Repositories\DeliveryRepository;
use App\Modules\Purchase\Repositories\PurchasedThreadRepository;
use App\Modules\Sales\Models\PurchaseDelivery;
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
        if ($this->checkQuantityNotExists($purchaseOrder, $input['orders'])) {
            return $this->sendResponse(null, __('messages.quantity_not_exists'),
                HTTPCode::UNPROCESSABLE_ENTITY);
        }
        $input['status_id'] = $this->masterRepository->findByCode(MasterConstant::SO_PENDING)->id;
        $input['delivery_no'] = $this->generateUniqueId(GenerateNumber::PO_DELIVERY);
        try {
            DB::beginTransaction();
            $delivery = $purchaseOrder->deliveries()->create($input);
            /** @var PurchaseDelivery $delivery */
            $delivery->partialOrders()->createMany($input['orders']);
            $this->storeStockDetails($purchaseOrder, $input['status_id']);
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
            if ($orderThreads->remaining_kg < $order->kg_qty) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param PurchaseOrder $purchaseOrder
     * @param               $pendingStatusId
     */
    private function storeStockDetails(PurchaseOrder $purchaseOrder, $statusId) {
        $purchaseOrder->orderStocks()->delete();
        $purchaseOrder->load([
            'orderRecipes.partialOrders.delivery',
        ]);
        $purchaseOrder->orderStocks()->createMany($this->getStockQuantity($purchaseOrder,
            $statusId));
    }

    /**
     * @param $purchaseOrder
     * @param $pendingStatusId
     * @return array
     */
    private function getStockQuantity($purchaseOrder, $pendingStatusId) {
        $stockQty = [];
        $formula = Formula::getInstance();
        $designDetail = $purchaseOrder->design->detail;
        $designPicks = $purchaseOrder->design->fiddlePicks;
        $beam = $purchaseOrder->designBeam->threadColor;
        foreach ($purchaseOrder->orderRecipes as $orderRecipe) {

            // create partial order stocks
            if ($orderRecipe->partialOrders->isNotEmpty()) {
                foreach ($orderRecipe->partialOrders as $partialOrder) {
                    // weft partial order per recipe thread color stock
                    $this->createStockQuantity($orderRecipe,
                        $partialOrder->delivery->status_id, $formula, $designDetail,
                        $partialOrder->total_meters, $designPicks, $stockQty, $partialOrder);

                    // warp partial order per recipe thread color stock
                    $stockQty[] = $this->setStockArray($orderRecipe->id, $beam->id,
                        $partialOrder->delivery->status_id,
                        $formula->getTotalKgQty(ThreadType::WARP,
                            $beam->thread, $designDetail,
                            $partialOrder->total_meters), $partialOrder);
                }
            }

            $remainingMeters = ($orderRecipe->total_meters - $orderRecipe->partialOrders->sum('total_meters'));
            // create remaining order stocks
            if ($remainingMeters) {
                // weft remaining meters thread color stock
                $this->createStockQuantity($orderRecipe,
                    $pendingStatusId, $formula, $designDetail,
                    $remainingMeters, $designPicks, $stockQty);

                // warp remaining meters thread color stock
                $stockQty[] = $this->setStockArray($orderRecipe->id, $beam->id,
                    $pendingStatusId,
                    $formula->getTotalKgQty(ThreadType::WARP,
                        $beam->thread, $designDetail,
                        $remainingMeters));

            }

        }

        return $stockQty;
    }

    /**
     * @param         $orderRecipe
     * @param         $statusId
     * @param Formula $formula
     * @param         $designDetail
     * @param         $totalMeters
     * @param         $designPicks
     * @param         $stockQty
     * @param bool    $partialOrder
     */
    private function createStockQuantity(
        $orderRecipe,
        $statusId,
        Formula $formula,
        $designDetail,
        $totalMeters,
        $designPicks,
        &$stockQty,
        $partialOrder = false
    ) {
        foreach ($orderRecipe->recipe->fiddles as $threadColorKey => $threadColor) {
            $threadColor->thread->pick = $designPicks[$threadColorKey]->pick;
            $stockQty[] = $this->setStockArray($orderRecipe->id, $threadColor['id'],
                $statusId,
                $formula->getTotalKgQty(ThreadType::WEFT,
                    $threadColor->thread, $designDetail,
                    $totalMeters), $partialOrder);
        }
    }

    /**
     * @param      $orderRecipeId
     * @param      $threadColorId
     * @param      $statusId
     * @param      $kgQty
     * @param bool $partialOrder
     * @return array
     */
    private function setStockArray(
        $orderRecipeId,
        $threadColorId,
        $statusId,
        $kgQty,
        $partialOrder = false
    ) {
        $stock = [
            'order_recipe_id' => $orderRecipeId,
            'product_id'      => $threadColorId,
            'product_type'    => 'thread_color',
            'status_id'       => $statusId,
            'kg_qty'          => $kgQty,
        ];

        if ($partialOrder) {
            /** @var PurchasePartialOrder $partialOrder */
            $stock['partial_order_id'] = $partialOrder->id;
        }

        return $stock;
    }


}
