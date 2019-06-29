<?php

namespace App\Modules\Sales\Http\Controllers;

use App\Constants\Master as MasterConstant;
use App\Http\Controllers\Controller;
use App\Modules\Design\Repositories\DesignDetailRepository;
use App\Modules\Sales\Http\Requests\Delivery\CreateRequest;
use App\Modules\Sales\Models\Delivery;
use App\Modules\Sales\Models\SalesOrder;
use App\Modules\Sales\Repositories\DeliveryRepository;
use App\Modules\Sales\Repositories\SalesRecipeRepository;
use App\Modules\Stock\Repositories\StockRepository;
use App\Modules\Thread\Constants\ThreadType;
use App\Repositories\MasterRepository;
use App\Support\Formula;
use DB;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Knovators\Support\Helpers\HTTPCode;
use Log;

/**
 * Class DeliveryController
 * @package App\Modules\Sales\Http\Controllers
 */
class DeliveryController extends Controller
{

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
        try {
            DB::beginTransaction();
            $delivery = $salesOrder->delivery()->create($input);
            /** @var Delivery $delivery */
            $delivery->partialOrders()->createMany($input['orders']);
            $this->storeStockDetails($salesOrder, $input['orders'], $input['status_id']);

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
     * @param SalesOrder $salesOrder
     * @param            $deliveryOrders
     * @param            $pendingStatusId
     */
    private function storeStockDetails(SalesOrder $salesOrder, $deliveryOrders, $pendingStatusId) {
        $salesRecipeIds = array_unique(array_column($deliveryOrders, 'sales_order_recipe_id'));
        $this->stockRepository->removeByPartialOrderId($salesRecipeIds);
        $orderRecipes = $this->orderRecipeRepository->with([
            'partialOrders.delivery',
            'recipe.fiddles.thread'
        ])->findWhereIn('id', $salesRecipeIds);

        $salesOrder->load([
            'design.detail',
            'design.fiddlePicks' => function ($fiddlePicks) {
                /** @var Builder $fiddlePicks */
                $fiddlePicks->orderBy('fiddle_no');
            },
            'designBeam.threadColor.thread'
        ]);
        $salesOrder->orderStocks()->createMany($this->getStockQuantity($salesOrder,
            $orderRecipes, $pendingStatusId));
    }


    /**
     * @param $salesOrder
     * @param $orderRecipes
     * @param $pendingStatusId
     * @return array
     */
    private function getStockQuantity($salesOrder, $orderRecipes, $pendingStatusId) {
        $stockQty = [];
        $formula = Formula::getInstance();
        $designDetail = $salesOrder->design->detail;
        $designPicks = $salesOrder->design->fiddlePicks;
        $beam = $salesOrder->designBeam->threadColor;
        foreach ($orderRecipes as $orderRecipe) {

            // create partial order stocks
            if ($orderRecipe->partialOrders->isNotEmpty()) {
                foreach ($orderRecipe->partialOrders as $partialOrder) {
                    // weft partial order per recipe thread color stock
                    $this->createStockQuantity($orderRecipe,
                        $partialOrder->delivery->status_id, $formula, $designDetail,
                        $partialOrder->total_meters, $designPicks);

                    // warp partial order per recipe thread color stock
                    $stockQty[] = $this->setStockArray($orderRecipe->id, $beam->id,
                        $partialOrder->delivery->status_id,
                        $formula->getTotalKgQty(ThreadType::WARP,
                            $beam->thread, $designDetail,
                            $partialOrder->total_meters));
                }
            }

            $remainingMeters = ($orderRecipe->total_meters - $orderRecipe->partialOrders->sum('total_meters'));
            // create remaining order stocks
            if ($remainingMeters) {
                // weft remaining meters thread color stock
                $this->createStockQuantity($orderRecipe,
                    $pendingStatusId, $formula, $designDetail,
                    $remainingMeters, $designPicks);

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
     */
    private function createStockQuantity(
        $orderRecipe,
        $statusId,
        Formula $formula,
        $designDetail,
        $totalMeters,
        $designPicks
    ) {
        foreach ($orderRecipe->recipe->fiddles as $threadColorKey => $threadColor) {
            $threadColor->thread->pick = $designPicks[$threadColorKey]->pick;
            $stockQty[] = $this->setStockArray($orderRecipe->id, $threadColor['id'],
                $statusId,
                $formula->getTotalKgQty(ThreadType::WEFT,
                    $threadColor->thread, $designDetail,
                    $totalMeters));
        }
    }


    /**
     * @param $orderRecipeId
     * @param $threadColorId
     * @param $statusId
     * @param $kgQty
     * @return array
     */
    private function setStockArray($orderRecipeId, $threadColorId, $statusId, $kgQty) {
        return [
            'order_recipe_id' => $orderRecipeId,
            'product_id'      => $threadColorId,
            'product_type'    => 'thread_color',
            'status_id'       => $statusId,
            'kg_qty'          => $kgQty,
        ];
    }


    /**
     * @param $salesOrder
     * @param $orders
     * @return bool
     */
    private function checkQuantityNotExists($salesOrder, $orders) {
        $orders = collect($orders)->groupBy('sales_order_recipe_id');
        $orderRecipes = $this->orderRecipeRepository->getOrderRecipeList($salesOrder->id);
        foreach ($orders as $key => $order) {
            $totalMeters = $order->sum('total_meters');
            $orderRecipe = $orderRecipes->find($key);
            if ($orderRecipe->remaining_meters < $totalMeters) {
                return true;
            }
        }

        return false;
    }

}
