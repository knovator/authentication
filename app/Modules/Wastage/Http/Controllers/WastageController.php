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
use App\Modules\Wastage\Http\Requests\StatusRequest;
use App\Modules\Wastage\Http\Requests\UpdateRequest;
use App\Modules\Wastage\Models\WastageOrder;
use App\Modules\Wastage\Models\WastageOrderRecipe;
use App\Modules\Wastage\Repositories\WastageOrderRecipeRepository;
use App\Modules\Wastage\Repositories\WastageOrderRepository;
use App\Support\DestroyObject;
use App\Support\Formula;
use App\Support\UniqueIdGenerator;
use Arr;
use Barryvdh\Snappy\Facades\SnappyPdf;
use DB;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Knovators\Masters\Repository\MasterRepository;
use Knovators\Support\Helpers\HTTPCode;
use Log;
use Maatwebsite\Excel\Facades\Excel;
use Prettus\Repository\Exceptions\RepositoryException;
use Prettus\Validator\Exceptions\ValidatorException;
use Str;
use App\Modules\Wastage\Http\Resources\WastageOrder as WastageOrderResource;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use App\Modules\Wastage\Http\Exports\WastageOrder as ExportWastageOrder;

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
                $input['type'] = 'wastage';
                $recipe = $this->recipeRepository->create($input);
                $fiddles = collect($input['thread_color_ids'])->map(function ($threadColor) {
                    unset($threadColor['denier']);
                    unset($threadColor['pick']);

                    return $threadColor;
                })->all();
                $recipe->fiddles()->attach($fiddles);
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
        $wastageOrder->load(['beam.thread:id,name,denier']);
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
            $this->wastageOrderRecipeRepo->deleteRecipeById($input['removed_order_recipes_id']);
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
     * @param WastageOrder $wastageOrder
     * @return Response
     */
    public function exportSummary(WastageOrder $wastageOrder) {
        $wastageOrder->load([
            'orderRecipes' => function ($orderRecipes) {
                /** @var Builder $orderRecipes */
                $orderRecipes->orderBy('id')->with([
                    'recipe.fiddles' => function ($fiddles) {
                        /** @var Builder $fiddles */
                        $fiddles->where('recipes_fiddles.fiddle_no', '=', 1)->with('color');
                    }
                ]);
            },
            'manufacturingCompany',
            'design.detail',
            'design.mainImage.file',
            'customer.state'
        ]);
        $isInvoice = true;

        return SnappyPdf::loadView('receipts.wastage-orders.main_summary.summary',
            compact('wastageOrder','isInvoice'));
    }

    /**
     * @param WastageOrder $wastageOrder
     * @return JsonResponse
     */
    public function show(WastageOrder $wastageOrder) {

        $wastageOrder->load([
            'beam.thread',
            'beam.color',
            'status',
            'customer.state:id,name,code',
            'design.detail',
            'design.mainImage.file',
            'fiddlePicks',
            'orderRecipes.recipe.fiddles.thread',
            'orderRecipes.recipe.fiddles.color',
            'manufacturingCompany'
        ]);

        return $this->sendResponse($this->makeResource($wastageOrder),
            __('messages.retrieved', ['module' => 'Wastage Order']),
            HTTPCode::OK);
    }

    /**
     * @param $wastageOrder
     * @return WastageOrderResource
     */
    private function makeResource($wastageOrder) {
        return new WastageOrderResource($wastageOrder);
    }

    /**
     * @param WastageOrder  $wastageOrder
     * @param UpdateRequest $request
     * @return mixed
     * @throws Exception
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
                __('messages.updated', ['module' => 'Wastage Order']),
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
                $wastageOrder->{$relation}()->whereId($item['id'])->update($item);
            } else {
                $newItems[] = $item;
            }
        }
        if (!empty($newItems)) {
            $wastageOrder->{$relation}()->createMany($newItems);
        }
        $removableLabel = 'removed_' . $column . '_id';

        if (isset($input[$removableLabel]) && !empty($input[$removableLabel])) {
            $wastageOrder->{$relation}()->whereIn('id', $input[$removableLabel])->delete();
        }
    }

    /**
     * @param WastageOrder $wastageOrder
     * @return JsonResponse
     */
    public function destroy(WastageOrder $wastageOrder) {
        try {
            $wastageOrder->load('status');
            if (($wastageOrder->status->code === MasterConstant::WASTAGE_PENDING) || $wastageOrder->status->code === MasterConstant::WASTAGE_CANCELED) {
                return $this->destroyModelObject([], $wastageOrder, 'Wastage Order');
            }

            return $this->sendResponse($wastageOrder,
                __('messages.not_delete_order', ['status' => $wastageOrder->status->name]),
                HTTPCode::UNPROCESSABLE_ENTITY);

        } catch (Exception $exception) {
            Log::error($exception);

            return $this->sendResponse(null, __('messages.something_wrong'),
                HTTPCode::UNPROCESSABLE_ENTITY, $exception);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request) {
        try {
            $orders = $this->wastageOrderRepository->wastageOrderList($request->all());

            return $this->sendResponse($orders,
                __('messages.retrieved', ['module' => 'Wastage Orders']),
                HTTPCode::OK);
        } catch (Exception $exception) {
            Log::error($exception);

            return $this->sendResponse(null, __('messages.something_wrong'),
                HTTPCode::UNPROCESSABLE_ENTITY, $exception);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse|BinaryFileResponse
     */
    public function exportCsv(Request $request) {
        try {
            $orders = $this->wastageOrderRepository->wastageOrderList($request->all(), true);
            if (($orders = collect($orders->getData()->data))->isEmpty()) {
                return $this->sendResponse(null,
                    __('messages.can_not_export', ['module' => 'Sales orders']),
                    HTTPCode::OK);
            }

            return Excel::download(new ExportWastageOrder($orders), 'wastage-orders.xlsx');
        } catch (Exception $exception) {
            Log::error($exception);

            return $this->sendResponse(null, __('messages.something_wrong'),
                HTTPCode::UNPROCESSABLE_ENTITY, $exception);
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
            $wastageOrder = $this->wastageOrderRepository->find($request->get('sales_order_id'));

            return $this->{$method}($wastageOrder, $request->all());
        } catch (Exception $exception) {
            Log::error('Unable to find status method: ' . $status);

            return $this->sendResponse(null, __('messages.something_wrong'),
                HTTPCode::UNPROCESSABLE_ENTITY, $exception);
        }

    }

    /**
     * @param WastageOrder  $wastageOrder
     * @param               $input
     * @return JsonResponse
     * @throws Exception
     */
    private function updateWASTAGEPENDINGStatus(WastageOrder $wastageOrder, $input) {
        $input['status_id'] = $this->masterRepository->findByCode(MasterConstant::WASTAGE_PENDING)->id;
        $wastageOrder->orderStocks()->update(['status_id' => $input['status_id']]);

        return $this->updateStatus($wastageOrder, $input);
    }

    /**
     * @param WastageOrder $wastageOrder
     * @param              $input
     * @return JsonResponse
     * @throws Exception
     */
    private function updateStatus(WastageOrder $wastageOrder, $input) {
        try {
            $wastageOrder->update($input);

            return $this->sendResponse($wastageOrder->refresh(),
                __('messages.updated', ['module' => 'Status']),
                HTTPCode::OK);
        } catch (Exception $exception) {
            Log::error($exception);

            throw $exception;
        }
    }

    /**
     * @param WastageOrder  $wastageOrder
     * @param               $input
     * @return JsonResponse
     * @throws Exception
     */
    private function updateWASTAGEDELIVEREDStatus(WastageOrder $wastageOrder, $input) {
        $input['status_id'] = $this->masterRepository->findByCode(MasterConstant::WASTAGE_DELIVERED)->id;
        $wastageOrder->orderStocks()->update(['status_id' => $input['status_id']]);

        return $this->updateStatus($wastageOrder, $input);
    }

    /**
     * @param WastageOrder  $wastageOrder
     * @param               $input
     * @return JsonResponse
     * @throws Exception
     */
    private function updateWASTAGECANCELEDStatus(WastageOrder $wastageOrder, $input) {
        $wastageOrder->orderStocks()->delete();
        $input['status_id'] = $this->masterRepository->findByCode(MasterConstant::WASTAGE_CANCELED)->id;

        return $this->updateStatus($wastageOrder, $input);
    }


}



