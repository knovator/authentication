<?php

namespace App\Modules\Wastage\Http\Controllers;

use App\Constants\GenerateNumber;
use App\Constants\Master;
use App\Constants\Master as MasterConstant;
use App\Http\Controllers\Controller;
use App\Modules\Design\Repositories\DesignDetailRepository;
use App\Modules\Recipe\Repositories\RecipeRepository;
use App\Modules\Thread\Constants\ThreadType;
use App\Modules\Wastage\Http\Requests\CreateRequest;
use App\Modules\Wastage\Http\Requests\UpdateRequest;
use App\Modules\Wastage\Models\WastageOrder;
use App\Modules\Wastage\Models\WastageOrderRecipe;
use App\Modules\Wastage\Repositories\WastageOrderRecipeRepository;
use App\Modules\Wastage\Repositories\WastageOrderRepository;
use App\Support\DestroyObject;
use App\Support\Formula;
use App\Support\UniqueIdGenerator;
use DB;
use Exception;
use Knovators\Masters\Repository\MasterRepository;
use Knovators\Support\Helpers\HTTPCode;
use Log;
use Prettus\Repository\Exceptions\RepositoryException;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 * Class WastageController
 * @package App\Modules\Wastage\Http\Controllers
 */
class WastageController extends Controller
{

    use DestroyObject, UniqueIdGenerator;

    protected $wastageOrderRepository;

    protected $masterRepository;

    protected $recipeRepository;

    protected $designDetailRepository;

    protected $wastageOrderRecipeRepo;

    /**
     * WastageController constructor
     * @param WastageOrderRepository       $wastageOrderRepository
     * @param MasterRepository             $masterRepository
     * @param RecipeRepository             $recipeRepository
     * @param DesignDetailRepository       $designDetailRepository
     * @param WastageOrderRecipeRepository $wastageOrderRecipeRepo
     */
    public function __construct(
        WastageOrderRepository $wastageOrderRepository,
        MasterRepository $masterRepository,
        RecipeRepository $recipeRepository,
        DesignDetailRepository $designDetailRepository,
        WastageOrderRecipeRepository $wastageOrderRecipeRepo
    ) {
        $this->wastageOrderRepository = $wastageOrderRepository;
        $this->masterRepository = $masterRepository;
        $this->recipeRepository = $recipeRepository;
        $this->designDetailRepository = $designDetailRepository;
        $this->wastageOrderRecipeRepo = $wastageOrderRecipeRepo;
    }

    /**
     * @param CreateRequest $request
     * @return mixed
     * @throws Exception
     */
    public function store(CreateRequest $request) {
        $input = $request->all();
        $input['order_no'] = $this->generateUniqueId(GenerateNumber::WASTAGE);
        $input['status_id'] = $this->masterRepository->findByCode(MasterConstant::WASTAGE_PENDING)->id;
        try {
            DB::beginTransaction();
            $this->findRecipeByFiddle($input['order_recipes']);
            $wastageOrder = $this->wastageOrderRepository->create($input);
            $wastageOrder->fiddlePicks()->createMany($input['fiddle_picks']);
            $wastageOrder->orderRecipes()->createMany($input['order_recipes']);
            $this->createOrUpdateOrderDetails($wastageOrder, $input);
            DB::commit();

            return $this->sendResponse($wastageOrder,
                __('messages.created', ['module' => 'Wastage Order']),
                HTTPCode::CREATED);
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error($exception);

            return $this->sendResponse(null, __('messages.something_wrong'),
                HTTPCode::UNPROCESSABLE_ENTITY, $exception);
        }
    }

    /**
     * @param $inputs
     * @throws RepositoryException
     * @throws ValidatorException
     */
    private function findRecipeByFiddle(&$inputs) {
        foreach ($inputs as $key => &$input) {
            $recipe = $this->recipeRepository->findUniqueNesRecipe($input);
            if (!$recipe) {
                $recipe = $this->recipeRepository->create($input)->id;
            }
            $input['recipe_id'] = $recipe->id;
        }
    }

    /**
     * @param            $wastageOrder
     * @param            $input
     * @param            $update
     * @throws RepositoryException
     */
    private function createOrUpdateOrderDetails(
        WastageOrder $wastageOrder,
        $input,
        $update = false
    ) {
        $designDetail = $this->designDetailRepository->findBy('design_id', $input['design_id'],
            ['panno', 'additional_panno', 'reed']);
        $wastageOrder->load(['beam.thread']);
        $this->storeWastageOrderRecipes($wastageOrder, $input, $designDetail, $update);
    }

    /**
     * @param WastageOrder $wastageOrder
     * @param              $input
     * @param              $designDetail
     * @param              $update
     */
    private function storeWastageOrderRecipes(
        WastageOrder $wastageOrder,
        $input,
        $designDetail,
        $update
    ) {
        if ($update) {
            $wastageOrder->orderStocks()->delete();
        }
        foreach ($input['order_recipes'] as $items) {
            $orderRecipeId = isset($items['id']) ? $items['id'] : null;
            $orderRecipe = $wastageOrder->orderRecipes()
                                        ->updateOrCreate(['id' => $orderRecipeId], $items);
            $this->storeRecipeOrderQuantities($wastageOrder, $orderRecipe, $items, $designDetail);
        }

        if ($update && isset($input['removed_order_recipes_id']) && !empty($input['removed_order_recipes_id'])) {
            $this->wastageOrderRecipeRepo->deleteWhere(['id', $input['removed_order_recipes_id']]);
        }
    }

    /**
     * @param WastageOrder       $wastageOrder
     * @param WastageOrderRecipe $orderRecipe
     * @param                    $items
     * @param                    $designDetail
     */
    private function storeRecipeOrderQuantities(
        WastageOrder $wastageOrder,
        WastageOrderRecipe $orderRecipe,
        $items,
        $designDetail
    ) {
        $formula = Formula::getInstance();
        $data = [];
        // storing weft stock
        foreach ($items['thread_color_ids'] as $key => $quantityDetails) {

            $data[$key] = [
                'wastage_recipe_id' => $orderRecipe->id,
                'product_id'        => $quantityDetails['thread_color_id'],
                'product_type'      => 'thread_color',
                'status_id'         => $wastageOrder->status_id,
                'kg_qty'            => -1 * $formula->getTotalKgQty(ThreadType::WEFT,
                        $quantityDetails, $designDetail, $items['total_meters']),
            ];
        }
        $threadDetail['denier'] = $wastageOrder->beam->thread->denier;
        // storing warp stock
        array_push($data, [
            'wastage_recipe_id' => $orderRecipe->id,
            'product_id'        => $wastageOrder->beam_id,
            'product_type'      => 'thread_color',
            'status_id'         => $wastageOrder->status_id,
            'kg_qty'            => -1 * $formula->getTotalKgQty(ThreadType::WARP,
                    $threadDetail, $designDetail, $items['total_meters']),
        ]);

        $wastageOrder->orderStocks()->createMany($data);
    }

    /**
     * @param WastageOrder  $wastageOrder
     * @param UpdateRequest $request
     * @return mixed
     */
    public function update(WastageOrder $wastageOrder, UpdateRequest $request) {
        $input = $request->all();
        try {
            DB::beginTransaction();
            $this->findRecipeByFiddle($input['order_recipes']);
            $wastageOrder->update($input);
            $this->storeWastageAttributes($wastageOrder, $input, 'fiddle_picks', 'fiddlePicks');
            $this->createOrUpdateOrderDetails($wastageOrder->refresh(), $input, true);
            DB::commit();

            return $this->sendResponse($wastageOrder,
                __('messages.updated', ['module' => 'Wastage order']),
                HTTPCode::OK);
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error($exception);

            return $this->sendResponse(null, __('messages.something_wrong'),
                HTTPCode::UNPROCESSABLE_ENTITY, $exception);
        }
    }

    /**
     * @param WastageOrder $wastageOrder
     * @param              $input
     * @param              $column
     * @param              $relation
     */
    private function storeWastageAttributes(
        WastageOrder $wastageOrder,
        $input,
        $column,
        $relation
    ) {
        $newItems = [];
        foreach ($input[$column] as $item) {
            if (isset($item['id'])) {
                $wastageOrder->{$relation()}->whereId($item['id'])->update($item);
            } else {
                $newItems[] = $item;
            }
        }
        if (!empty($newItems)) {
            $wastageOrder->{$relation()}->createMany($newItems);
        }
        $removableLabel = 'removed_' . $column . '_id';

        if (isset($input[$removableLabel]) && !empty($input[$removableLabel])) {
            $wastageOrder->{$relation()}->whereIn('id', $input[$removableLabel])->delete();
        }
    }


}



