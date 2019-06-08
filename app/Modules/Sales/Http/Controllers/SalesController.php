<?php

namespace App\Modules\Sales\Http\Controllers;

use App\Constants\GenerateNumber;
use App\Constants\Master as MasterConstant;
use App\Http\Controllers\Controller;
use App\Modules\Design\Repositories\DesignDetailRepository;
use App\Modules\Sales\Http\Requests\CreateRequest;
use App\Modules\Sales\Http\Requests\StatusRequest;
use App\Modules\Sales\Http\Requests\UpdateRequest;
use App\Modules\Sales\Http\Resources\SalesOrder as SalesOrderResource;
use App\Modules\Sales\Models\RecipePartialOrder;
use App\Modules\Sales\Models\SalesOrder;
use App\Modules\Sales\Models\SalesOrderRecipe;
use App\Modules\Sales\Repositories\RecipePartialRepository;
use App\Modules\Sales\Repositories\SalesOrderRepository;
use App\Modules\Sales\Repositories\SalesRecipeRepository;
use App\Modules\Stock\Repositories\StockRepository;
use App\Modules\Thread\Constants\ThreadType;
use App\Support\Formula;
use App\Support\UniqueIdGenerator;
use DB;
use Exception;
use Illuminate\Container\Container;
use Illuminate\Http\JsonResponse;
use Knovators\Masters\Repository\MasterRepository;
use Knovators\Support\Helpers\HTTPCode;
use Knovators\Support\Traits\DestroyObject;
use Log;
use Prettus\Repository\Exceptions\RepositoryException;
use Str;

/**
 * Class SalesController
 * @package App\Modules\Sales\Http\Controllers
 */
class SalesController extends Controller
{

    use DestroyObject, UniqueIdGenerator;

    protected $salesOrderRepository;

    protected $masterRepository;

    protected $designDetailRepository;

    protected $recipePartialOrderRepo;


    /**
     * SalesController constructor.
     * @param SalesOrderRepository    $salesOrderRepository
     * @param MasterRepository        $masterRepository
     * @param DesignDetailRepository  $designDetailRepository
     * @param RecipePartialRepository $recipePartialOrderRepository
     */
    public function __construct(
        SalesOrderRepository $salesOrderRepository,
        MasterRepository $masterRepository,
        DesignDetailRepository $designDetailRepository,
        RecipePartialRepository $recipePartialOrderRepository
    ) {
        $this->salesOrderRepository = $salesOrderRepository;
        $this->masterRepository = $masterRepository;
        $this->designDetailRepository = $designDetailRepository;
        $this->recipePartialOrderRepo = $recipePartialOrderRepository;
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
            $input['order_no'] = $this->generateUniqueId(GenerateNumber::SALES);
            $input['status_id'] = $this->getMasterByCode(MasterConstant::SO_PENDING);
            $salesOrder = $this->salesOrderRepository->create($input);
            $this->createOrUpdateSalesDetails($salesOrder, $input, false);
            DB::commit();

            return $this->sendResponse($this->makeResource($salesOrder),
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
     * @param SalesOrder $salesOrder
     * @param            $input
     * @param            $update
     * @throws RepositoryException
     */
    private function createOrUpdateSalesDetails(SalesOrder $salesOrder, $input, $update) {
        $designDetail = $this->designDetailRepository->findBy('design_id',
            $input['design_id'], ['panno', 'additional_panno', 'reed']);
        $salesOrder->load('designBeam.threadColor.thread');
        $this->storeSalesOrderRecipes($salesOrder, $input, $designDetail, $update);
    }


    /**
     * @param SalesOrder    $salesOrder
     * @param UpdateRequest $request
     * @return mixed
     * @throws Exception
     */
    public function update(SalesOrder $salesOrder, UpdateRequest $request) {
        $input = $request->all();
        try {
            DB::beginTransaction();
            $salesOrder->update($input);
            $salesOrder->fresh();
            $this->createOrUpdateSalesDetails($salesOrder, $input, true);
            DB::commit();

            return $this->sendResponse($this->makeResource($salesOrder),
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
     * @param SalesOrder $salesOrder
     * @param            $input
     * @param            $designDetail
     * @param            $update
     */
    private function storeSalesOrderRecipes(
        SalesOrder $salesOrder,
        $input,
        $designDetail,
        $update
    ) {
        foreach ($input['order_recipes'] as $items) {
            $orderRecipeId = isset($items['id']) ? $items['id'] : null;
            $orderRecipe = $salesOrder->orderRecipes()
                                      ->updateOrCreate(['id' => $orderRecipeId], $items);
            $items['status_id'] = $salesOrder->status_id;
            /** @var SalesOrderRecipe $orderRecipe */
            $partialOrder = $orderRecipe->partialOrders()->updateOrCreate([], $items);
            /** @var RecipePartialOrder $partialOrder */
            $items['designDetail'] = $designDetail;

            if ($update) {
                // remove old stock results
                $salesOrder->orderStocks()->delete();
            }
            $this->storeRecipeOrderQuantities($salesOrder, $partialOrder, $items);
        }

        if ($update && isset($input['removed_order_recipes_id']) && !empty($input['removed_order_recipes_id'])) {
            $this->destroyOrderRecipes($input['removed_order_recipes_id']);
        }
    }


    /**
     * @param $orderRecipeIds
     */
    private function destroyOrderRecipes($orderRecipeIds) {
        // get recipes default partial order
        $partialOrderIds = $this->recipePartialOrderRepo->findIdByRecipeIds($orderRecipeIds);
        // remove sales partial order stocks
        (new StockRepository(new Container()))->removeByPartialOrderId($partialOrderIds);
        // remove sales recipe default partial order
        $this->recipePartialOrderRepo->removeById($partialOrderIds);
        // remove sales partial orders
        (new SalesRecipeRepository(new Container()))->removeById($orderRecipeIds);

    }

    /**
     * @param SalesOrder         $salesOrder
     * @param RecipePartialOrder $partialOrder
     * @param                    $items
     */
    private function storeRecipeOrderQuantities(
        SalesOrder $salesOrder,
        RecipePartialOrder $partialOrder,
        $items
    ) {
        $formula = Formula::getInstance();
        $data = [];
        // storing weft stock
        foreach ($items['quantity_details'] as $key => $quantityDetails) {

            $data[$key] = [
                'partial_order_id' => $partialOrder->id,
                'product_id'       => $quantityDetails['thread_color_id'],
                'product_type'     => 'thread_color',
                'status_id'        => $items['status_id'],
                'kg_qty'           => $formula->getTotalKgQty(ThreadType::WEFT,
                    $quantityDetails, $items),
            ];
        }

        $threadDetail['denier'] = $salesOrder->designBeam->threadColor->thread->denier;
        // storing warp stock
        array_push($data, [
            'partial_order_id' => $partialOrder->id,
            'product_id'       => $salesOrder->designBeam->thread_color_id,
            'product_type'     => 'thread_color',
            'status_id'        => $items['status_id'],
            'kg_qty'           => $formula->getTotalKgQty(ThreadType::WARP,
                $threadDetail, $items),
        ]);

        $salesOrder->orderStocks()->createMany($data);
    }


    /**
     * @param $code
     * @return integer
     */
    private function getMasterByCode($code) {
        return $this->masterRepository->findByCode($code)->id;
    }


    /**
     * @param SalesOrder $salesOrder
     * @return JsonResponse
     */
    public function destroy(SalesOrder $salesOrder) {
        try {
            $salesOrder->load('status');
            if (($salesOrder->status->code === MasterConstant::SO_PENDING) ||
                $salesOrder->status->code === MasterConstant::SO_CANCELED) {
                return $this->destroyModelObject([], $salesOrder, 'Sales Order');
            }

            return $this->sendResponse($this->makeResource($salesOrder),
                __('messages.not_delete_sales_order', ['status' => $salesOrder->status->name]),
                HTTPCode::UNPROCESSABLE_ENTITY);

        } catch (Exception $exception) {
            Log::error($exception);

            return $this->sendResponse(null, __('messages.something_wrong'),
                HTTPCode::UNPROCESSABLE_ENTITY, $exception);
        }
    }


    /**
     * @param SalesOrder $salesOrder
     * @return JsonResponse
     */
    public function show(SalesOrder $salesOrder) {
        $salesOrder->load([
            'customer',
            'status',
            'design',
            'designBeam.threadColor.thread',
            'designBeam.threadColor.color',
            'orderRecipes',
            'orderRecipes.recipe.fiddles.thread',
            'orderRecipes.recipe.fiddles.color'
        ]);

        return $this->sendResponse($this->makeResource($salesOrder),
            __('messages.retrieved', ['module' => 'Sales Order']),
            HTTPCode::OK);
    }


    /**
     * @return JsonResponse
     */
    public function index() {
        try {
            $orders = $this->salesOrderRepository->getSalesOrderList();

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
     * @param $salesOrder
     * @return SalesOrderResource
     */
    private function makeResource($salesOrder) {
        return new SalesOrderResource($salesOrder);
    }

    /**
     * @param StatusRequest $request
     * @return JsonResponse
     */
    public function changeStatus(StatusRequest $request) {
        $status = $request->get('code');
        $method = 'update' . Str::studly($status) . 'Status';
        try {
            $salesOrder = $this->salesOrderRepository->find($request->get('sales_order_id'));

            return $this->{$method}($salesOrder, $request->all());
        } catch (Exception $exception) {
            Log::error('Unable to find status method: ' . $status);

            return $this->sendResponse(null, __('messages.something_wrong'),
                HTTPCode::UNPROCESSABLE_ENTITY, $exception);
        }

    }

}
