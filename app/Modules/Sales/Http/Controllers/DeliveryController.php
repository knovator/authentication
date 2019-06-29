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
            $this->storeStockDetails($salesOrder, $input['orders']);

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
     * @param $salesOrder
     * @param $deliveryOrders
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    private function storeStockDetails(SalesOrder $salesOrder, $deliveryOrders) {
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
        $salesOrder->orderStocks()->createMany($this->createStockQuantity($salesOrder,
            $orderRecipes));
    }


    /**
     * @param $salesOrder
     * @param $orderRecipes
     * @param $designDetail
     * @return array
     */
    private function createStockQuantity($salesOrder, $orderRecipes) {
        $stockQty = [];
        $formula = Formula::getInstance();
        $designDetail = $salesOrder->design->detail;
        $designPicks = $salesOrder->design->fiddlePicks;
        foreach ($orderRecipes as $orderRecipe) {
            if ($orderRecipe->partialOrders->isNotEmpty()) {
                foreach ($orderRecipe->partialOrders as $partialOrder) {
                    $threadColors = $orderRecipe->recipe->fiddles;
                    foreach ($threadColors as $threadColorKey => $threadColor) {
                        $threadColor->thread->pick = $designPicks[$threadColorKey]->pick;
                        $stockQty[] = [
                            'order_recipe_id' => $orderRecipe->id,
                            'product_id'      => $threadColor['id'],
                            'product_type'    => 'thread_color',
                            'status_id'       => $partialOrder->delivery->status_id,
                            'kg_qty'          => $formula->getTotalKgQty(ThreadType::WEFT,
                                $threadColor->thread, $designDetail,
                                $partialOrder->total_meters),
                        ];
                    }
                    $beam = $salesOrder->designBeam->threadColor;
                    $stockQty[] = [
                        'order_recipe_id' => $orderRecipe->id,
                        'product_id'      => $beam->id,
                        'product_type'    => 'thread_color',
                        'status_id'       => $partialOrder->delivery->status_id,
                        'kg_qty'          => $formula->getTotalKgQty(ThreadType::WARP,
                            $beam->thread, $designDetail,
                            $partialOrder->total_meters),
                    ];
                }
            }
        }
        return $stockQty;
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
