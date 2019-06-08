<?php

namespace App\Modules\Sales\Http\Controllers;

use App\Constants\GenerateNumber;
use App\Constants\Master as MasterConstant;
use App\Http\Controllers\Controller;
use App\Modules\Design\Repositories\DesignDetailRepository;
use App\Modules\Sales\Http\Requests\CreateRequest;
use App\Modules\Sales\Http\Requests\UpdateRequest;
use App\Modules\Sales\Models\RecipePartialOrder;
use App\Modules\Sales\Models\SalesOrder;
use App\Modules\Sales\Models\SalesOrderRecipe;
use App\Modules\Sales\Repositories\RecipePartialRepository;
use App\Modules\Sales\Repositories\SalesRecipeQuantityRepository;
use App\Modules\Sales\Repositories\SalesRecipeRepository;
use App\Modules\Sales\Repositories\SalesOrderRepository;
use App\Modules\Stock\Repositories\StockRepository;
use App\Modules\Thread\Constants\ThreadType;
use App\Support\Formula;
use App\Support\UniqueIdGenerator;
use DB;
use Exception;
use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Builder;
use Knovators\Masters\Repository\MasterRepository;
use Knovators\Support\Helpers\HTTPCode;
use Knovators\Support\Traits\DestroyObject;
use Log;
use Prettus\Repository\Exceptions\RepositoryException;
use function Zend\Diactoros\normalizeUploadedFiles;

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

            return $this->sendResponse($salesOrder,
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

            return $this->sendResponse($salesOrder,
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
                // remove old quantities and stock results
                $orderRecipe->quantities()->delete();
                $salesOrder->orderStocks()->delete();
            }

            $this->storeRecipeOrderQuantities($salesOrder, $orderRecipe, $partialOrder, $items);
        }

        if ($update && isset($input['removed_order_recipes_id']) && !empty($input['removed_order_recipes_id'])) {
            $this->destroyOrderRecipes($input['removed_order_recipes_id']);
        }
    }


    /**
     * @param $orderRecipeIds
     */
    private function destroyOrderRecipes($orderRecipeIds) {
        // remove sales recipes quantity
        (new SalesRecipeQuantityRepository(new Container()))->removeByOrderRecipesId($orderRecipeIds);
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
     * @param SalesOrderRecipe   $orderRecipe
     * @param RecipePartialOrder $partialOrder
     * @param                    $items
     */
    private function storeRecipeOrderQuantities(
        SalesOrder $salesOrder,
        SalesOrderRecipe $orderRecipe,
        RecipePartialOrder $partialOrder,
        $items
    ) {


        $formula = Formula::getInstance();
        $data = [];
        // storing weft stock
        foreach ($items['quantity_details'] as $key => $quantityDetails) {

            $data[$key] = [
                'partial_order_id' => $partialOrder->id,
                'fiddle_no'        => $quantityDetails['fiddle_no'],
                'thread_color_id'  => $quantityDetails['thread_color_id'],
                'product_id'       => $quantityDetails['thread_color_id'],
                'product_type'     => 'thread_color',
                'kg_qty'           => $formula->getTotalKgQty(ThreadType::WEFT,
                    $quantityDetails, $items),
                'status_id'        => $items['status_id'],
            ];
        }
        $orderRecipe->quantities()->createMany($data);

        $threadDetail['denier'] = $salesOrder->designBeam->threadColor->thread->denier;
        // storing warp stock
        array_push($data, [
            'product_id'       => $salesOrder->designBeam->thread_color_id,
            'product_type'     => 'thread_color',
            'status_id'        => $items['status_id'],
            'partial_order_id' => $partialOrder->id,
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


}
