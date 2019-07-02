<?php

namespace App\Modules\Sales\Http\Controllers;

use App\Constants\GenerateNumber;
use App\Constants\Master as MasterConstant;
use App\Http\Controllers\Controller;
use App\Modules\Design\Repositories\DesignDetailRepository;
use App\Modules\Sales\Http\Requests\Delivery\CreateRequest;
use App\Modules\Sales\Http\Requests\Delivery\UpdateRequest;
use App\Modules\Sales\Models\Delivery;
use App\Modules\Sales\Models\RecipePartialOrder;
use App\Modules\Sales\Models\SalesOrder;
use App\Modules\Sales\Repositories\DeliveryRepository;
use App\Modules\Sales\Repositories\SalesRecipeRepository;
use App\Modules\Stock\Repositories\StockRepository;
use App\Modules\Thread\Constants\ThreadType;
use App\Repositories\MasterRepository;
use App\Support\Formula;
use App\Support\UniqueIdGenerator;
use DB;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Knovators\Support\Helpers\HTTPCode;
use Knovators\Support\Traits\DestroyObject;
use Log;

/**
 * Class DeliveryController
 * @package App\Modules\Sales\Http\Controllers
 */
class DeliveryController extends Controller
{

    use DestroyObject, UniqueIdGenerator;

    protected $deliveryRepository;

    protected $masterRepository;

    protected $orderRecipeRepository;

    protected $stockRepository;

    protected $designDetailRepository;

    /**
     * DeliveryController constructor.
     * @param DeliveryRepository     $deliveryRepository
     * @param MasterRepository       $masterRepository
     * @param SalesRecipeRepository  $orderRecipeRepository
     * @param StockRepository        $stockRepository
     * @param DesignDetailRepository $designDetailRepository
     */
    public function __construct(
        DeliveryRepository $deliveryRepository,
        MasterRepository $masterRepository,
        SalesRecipeRepository $orderRecipeRepository,
        StockRepository $stockRepository,
        DesignDetailRepository $designDetailRepository
    ) {
        $this->deliveryRepository = $deliveryRepository;
        $this->masterRepository = $masterRepository;
        $this->orderRecipeRepository = $orderRecipeRepository;
        $this->stockRepository = $stockRepository;
        $this->designDetailRepository = $designDetailRepository;
    }


    /**
     * @param SalesOrder    $salesOrder
     * @param CreateRequest $request
     * @return mixed
     * @throws Exception
     */
    public function store(SalesOrder $salesOrder, CreateRequest $request) {
        $input = $request->all();
        if ($this->checkQuantityNotExists($salesOrder, $input['orders'])) {
            return $this->sendResponse(null, __('messages.quantity_not_exists'),
                HTTPCode::UNPROCESSABLE_ENTITY);
        }
        $input['status_id'] = $this->masterRepository->findByCode(MasterConstant::SO_PENDING)->id;
        $input['delivery_no'] = $this->generateUniqueId(GenerateNumber::DELIVERY);
        try {
            DB::beginTransaction();
            $delivery = $salesOrder->delivery()->create($input);
            /** @var Delivery $delivery */
            $delivery->partialOrders()->createMany($input['orders']);
            $this->storeStockDetails($salesOrder, $input['status_id']);
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
     * @param SalesOrder    $salesOrder
     * @param Delivery      $delivery
     * @param UpdateRequest $request
     * @return mixed
     * @throws Exception
     */
    public function update(SalesOrder $salesOrder, Delivery $delivery, UpdateRequest $request) {
        $input = $request->all();
        if ($this->checkQuantityNotExists($salesOrder, $input['orders'], $delivery->id)) {
            return $this->sendResponse(null, __('messages.quantity_not_exists'),
                HTTPCode::UNPROCESSABLE_ENTITY);
        }
        try {
            DB::beginTransaction();
            $delivery->update($input);
            $this->partialOrderUpdateOrCreate($delivery, $input);
            $this->storeStockDetails($salesOrder,
                $this->masterRepository->findByCode(MasterConstant::SO_PENDING)->id);
            DB::commit();

            return $this->sendResponse($delivery->fresh(),
                __('messages.updated', ['module' => 'Sales']),
                HTTPCode::OK);
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error($exception);

            return $this->sendResponse(null, __('messages.something_wrong'),
                HTTPCode::UNPROCESSABLE_ENTITY, $exception);
        }
    }


    /**
     * @param Delivery $delivery
     * @param array    $input
     */
    private function partialOrderUpdateOrCreate(Delivery $delivery, $input) {
        $partialOrders = [];
        foreach ($input['orders'] as $order) {
            if (isset($order['id'])) {
                $delivery->partialOrders()->whereId($order['id'])->update($order);
            } else {
                $partialOrders[] = $order;
            }
        }
        if (!empty($partialOrders)) {
            $delivery->partialOrders()->createMany($partialOrders);
        }
        if (isset($input['removed_partial_orders'])) {
            $delivery->partialOrders()->whereIn('id', $input['removed_partial_orders'])->delete();
        }
    }


    /**
     * @param SalesOrder $salesOrder
     * @param            $pendingStatusId
     */
    private function storeStockDetails(SalesOrder $salesOrder, $pendingStatusId) {
        $salesOrder->orderStocks()->delete();
        $salesOrder->load([
            'orderRecipes.partialOrders.delivery',
            'orderRecipes.recipe.fiddles.thread',
            'design.detail',
            'design.fiddlePicks' => function ($fiddlePicks) {
                /** @var Builder $fiddlePicks */
                $fiddlePicks->orderBy('fiddle_no');
            },
            'designBeam.threadColor.thread'
        ]);
        $salesOrder->orderStocks()->createMany($this->getStockQuantity($salesOrder,
            $pendingStatusId));
    }


    /**
     * @param $salesOrder
     * @param $pendingStatusId
     * @return array
     */
    private function getStockQuantity($salesOrder, $pendingStatusId) {
        $stockQty = [];
        $formula = Formula::getInstance();
        $designDetail = $salesOrder->design->detail;
        $designPicks = $salesOrder->design->fiddlePicks;
        $beam = $salesOrder->designBeam->threadColor;
        foreach ($salesOrder->orderRecipes as $orderRecipe) {

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
            /** @var RecipePartialOrder $partialOrder */
            $stock['partial_order_id'] = $partialOrder->id;
        }

        return $stock;
    }


    /**
     * @param      $salesOrder
     * @param      $orders
     * @param null $deliveryId
     * @return bool
     */
    private function checkQuantityNotExists($salesOrder, $orders, $deliveryId = null) {
        $orders = collect($orders)->groupBy('sales_order_recipe_id');
        $orderRecipes = $this->orderRecipeRepository->getOrderRecipeList($salesOrder->id,
            $deliveryId);
        foreach ($orders as $key => $order) {
            $totalMeters = $order->sum('total_meters');
            $orderRecipe = $orderRecipes->find($key);
            if ($orderRecipe->remaining_meters < $totalMeters) {
                return true;
            }
        }

        return false;
    }


    /**
     * @param SalesOrder $salesOrder
     * @param Delivery   $delivery
     * @return JsonResponse
     */
    public function destroy(SalesOrder $salesOrder, Delivery $delivery) {
        try {
            $delivery->load('status');
            if (($delivery->status->code === MasterConstant::SO_PENDING) ||
                $delivery->status->code === MasterConstant::SO_CANCELED) {
                $response = $this->destroyModelObject([], $delivery, 'Delivery');
                $this->storeStockDetails($salesOrder,
                    $this->masterRepository->findByCode(MasterConstant::SO_PENDING)->id);

                return $response;
            }

            return $this->sendResponse(null,
                __('messages.delivery_can_not_delete', ['status' => $delivery->status->name]),
                HTTPCode::UNPROCESSABLE_ENTITY);

        } catch (Exception $exception) {
            Log::error($exception);

            return $this->sendResponse(null, __('messages.something_wrong'),
                HTTPCode::UNPROCESSABLE_ENTITY, $exception);
        }
    }

}
