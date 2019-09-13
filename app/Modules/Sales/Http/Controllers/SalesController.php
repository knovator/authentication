<?php

namespace App\Modules\Sales\Http\Controllers;

use App\Constants\GenerateNumber;
use App\Constants\Master;
use App\Constants\Master as MasterConstant;
use App\Http\Controllers\Controller;
use App\Jobs\OrderFormJob;
use App\Modules\Design\Repositories\DesignDetailRepository;
use App\Modules\Sales\Http\Exports\SalesOrder as ExportSalesOrder;
use App\Modules\Sales\Http\Requests\AnalysisRequest;
use App\Modules\Sales\Http\Requests\CreateRequest;
use App\Modules\Sales\Http\Requests\MailRequest;
use App\Modules\Sales\Http\Requests\StatusRequest;
use App\Modules\Sales\Http\Requests\UpdateRequest;
use App\Modules\Sales\Http\Resources\SalesOrder as SalesOrderResource;
use App\Modules\Sales\Models\Delivery;
use App\Modules\Sales\Models\SalesOrder;
use App\Modules\Sales\Models\SalesOrderRecipe;
use App\Modules\Sales\Repositories\CompanyRepository;
use App\Modules\Sales\Repositories\DeliveryRepository;
use App\Modules\Sales\Repositories\RecipePartialRepository;
use App\Modules\Sales\Repositories\SalesOrderRepository;
use App\Modules\Sales\Repositories\SalesRecipeRepository;
use App\Modules\Sales\Support\ExportSaleOrderSummary;
use App\Modules\Stock\Repositories\StockRepository;
use App\Modules\Thread\Constants\ThreadType;
use App\Modules\Thread\Repositories\ThreadColorRepository;
use App\Repositories\MasterRepository;
use App\Support\DestroyObject;
use App\Support\Formula;
use App\Support\UniqueIdGenerator;
use DB;
use Exception;
use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Knovators\Support\Helpers\HTTPCode;
use Log;
use Maatwebsite\Excel\Facades\Excel;
use Prettus\Repository\Exceptions\RepositoryException;
use Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * Class SalesController
 * @package App\Modules\Sales\Http\Controllers
 */
class SalesController extends Controller
{

    use DestroyObject, UniqueIdGenerator, ExportSaleOrderSummary;

    protected $salesOrderRepository;

    protected $masterRepository;

    protected $designDetailRepository;

    protected $recipePartialOrderRepo;

    protected $threadColorRepository;

    protected $stockRepository;

    protected $deliveryRepository;


    /**
     * SalesController constructor.
     * @param SalesOrderRepository    $salesOrderRepository
     * @param MasterRepository        $masterRepository
     * @param DesignDetailRepository  $designDetailRepository
     * @param RecipePartialRepository $recipePartialOrderRepository
     * @param ThreadColorRepository   $threadColorRepository
     * @param StockRepository         $stockRepository
     * @param DeliveryRepository      $deliveryRepository
     */
    public function __construct(
        SalesOrderRepository $salesOrderRepository,
        MasterRepository $masterRepository,
        DesignDetailRepository $designDetailRepository,
        RecipePartialRepository $recipePartialOrderRepository,
        ThreadColorRepository $threadColorRepository,
        StockRepository $stockRepository,
        DeliveryRepository $deliveryRepository
    ) {
        $this->salesOrderRepository = $salesOrderRepository;
        $this->masterRepository = $masterRepository;
        $this->designDetailRepository = $designDetailRepository;
        $this->recipePartialOrderRepo = $recipePartialOrderRepository;
        $this->threadColorRepository = $threadColorRepository;
        $this->stockRepository = $stockRepository;
        $this->deliveryRepository = $deliveryRepository;
    }

    /**
     * @param CreateRequest $request
     * @return mixed
     * @throws Exception
     */
    public function store(CreateRequest $request) {
        $input = $request->all();
        if ($response = $this->uniqueCustomerPoNumber($request)) {
            return $response;
        }
        try {
            DB::beginTransaction();
            dd('here');
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
     * @param Request $request
     * @param         $ignoreId
     * @return bool|JsonResponse
     * @throws RepositoryException
     */
    private function uniqueCustomerPoNumber(Request $request, $ignoreId = false) {
        if ($request->has('customer_po_number')) {
            $oldOrder = $this->salesOrderRepository->makeModel()->where('customer_po_number',
                $request->get('customer_po_number'));
            /** @var Builder $oldOrder */
            if ($ignoreId) {
                $oldOrder = $oldOrder->whereKeyNot($ignoreId);
            }
            if ($oldOrder = $oldOrder->first()) {
                return $this->sendResponse(null,
                    'Customer po number is already exist in order no ' . $oldOrder->order_no,
                    HTTPCode::UNPROCESSABLE_ENTITY);
            }
        }

        return false;
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
     * @param            $input
     * @param            $update
     * @throws RepositoryException
     */
    private function createOrUpdateSalesDetails(SalesOrder $salesOrder, $input, $update) {
        $designDetail = $this->designDetailRepository->findBy('design_id',
            $input['design_id'], ['panno', 'additional_panno', 'reed']);
        $salesOrder->load(['designBeam.threadColor.thread', 'status']);
        $this->storeSalesOrderRecipes($salesOrder, $input, $designDetail, $update);
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
        if ($salesOrder->status->code != MasterConstant::SO_MANUFACTURING) {
            if ($update) {
                // remove old stock results
                $salesOrder->orderStocks()->delete();
            }

            foreach ($input['order_recipes'] as $items) {
                $orderRecipeId = isset($items['id']) ? $items['id'] : null;
                $orderRecipe = $salesOrder->orderRecipes()
                                          ->updateOrCreate(['id' => $orderRecipeId], $items);
                $this->storeRecipeOrderQuantities($salesOrder, $orderRecipe, $items, $designDetail);
            }
        }

        if ($update && isset($input['removed_order_recipes_id']) && !empty($input['removed_order_recipes_id'])) {
            $this->destroyOrderRecipes($input['removed_order_recipes_id'], $salesOrder);
        }
    }

    /**
     * @param SalesOrder         $salesOrder
     * @param SalesOrderRecipe   $orderRecipe
     * @param                    $items
     * @param                    $designDetail
     */
    private function storeRecipeOrderQuantities(
        SalesOrder $salesOrder,
        SalesOrderRecipe $orderRecipe,
        $items,
        $designDetail
    ) {
        $formula = Formula::getInstance();
        $data = [];
        // storing weft stock
        foreach ($items['quantity_details'] as $key => $quantityDetails) {

            $data[$key] = [
                'order_recipe_id' => $orderRecipe->id,
                'product_id'      => $quantityDetails['thread_color_id'],
                'product_type'    => 'thread_color',
                'status_id'       => $salesOrder->status_id,
                'kg_qty'          => -1 * $formula->getTotalKgQty(ThreadType::WEFT,
                        $quantityDetails, $designDetail, $items['total_meters']),
            ];
        }
        $threadDetail['denier'] = $salesOrder->designBeam->threadColor->thread->denier;
        // storing warp stock
        array_push($data, [
            'order_recipe_id' => $orderRecipe->id,
            'product_id'      => $salesOrder->designBeam->thread_color_id,
            'product_type'    => 'thread_color',
            'status_id'       => $salesOrder->status_id,
            'kg_qty'          => -1 * $formula->getTotalKgQty(ThreadType::WARP,
                    $threadDetail, $designDetail, $items['total_meters']),
        ]);

        $salesOrder->orderStocks()->createMany($data);
    }

    /**
     * @param $orderRecipeIds
     * @param $salesOrder
     */
    private function destroyOrderRecipes($orderRecipeIds, $salesOrder) {

        if ($salesOrder->status->code == MasterConstant::SO_MANUFACTURING) {
            $partialOrderIds = $this->recipePartialOrderRepo->findIdByRecipeIds($orderRecipeIds);
            $this->deliveryRepository->removeSinglePartialOrders($partialOrderIds, $salesOrder->id);
            $this->recipePartialOrderRepo->removeByField('id', $partialOrderIds);
        }
        // remove sales partial order stocks
        $this->stockRepository->removeByField('order_recipe_id', $orderRecipeIds);
        // remove sales partial orders
        (new SalesRecipeRepository(new Container()))->removeByField('id', $orderRecipeIds);

    }

    /**
     * @param $salesOrder
     * @return SalesOrderResource
     */
    private function makeResource($salesOrder) {
        return new SalesOrderResource($salesOrder);
    }

    /**
     * @param SalesOrder  $salesOrder
     * @param MailRequest $request
     * @return JsonResponse
     */
    public function sendMailToCustomer(SalesOrder $salesOrder, MailRequest $request) {
        $input = $request->all();
        $salesOrder->load('customer');
        try {
            if (is_null($salesOrder->customer->email)) {
                $salesOrder->customer()->update(['email' => $input['email']]);
                $salesOrder = $salesOrder->fresh();
            }
            OrderFormJob::dispatch($salesOrder)->delay(now()->addSeconds(10));

            return $this->sendResponse($this->makeResource($salesOrder),
                __('messages.created', ['module' => 'Mail']),
                HTTPCode::OK);
        } catch (Exception $exception) {
            Log::error($exception);

            return $this->sendResponse(null, __('messages.something_wrong'),
                HTTPCode::UNPROCESSABLE_ENTITY, $exception);
        }

    }

    /**
     * @param SalesOrder    $salesOrder
     * @param UpdateRequest $request
     * @return mixed
     * @throws Exception
     */
    public function update(SalesOrder $salesOrder, UpdateRequest $request) {
        $input = $request->all();
        if ($response = $this->uniqueCustomerPoNumber($request, $salesOrder->id)) {
            return $response;
        }
        try {
            DB::beginTransaction();
            $salesOrder->update($input);
            $this->createOrUpdateSalesDetails($salesOrder->refresh(), $input, true);
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
                __('messages.not_delete_order', ['status' => $salesOrder->status->name]),
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
        $relations = [
            'customer',
            'design.detail',
            'design.mainImage.file',
            'designBeam.threadColor.thread',
            'designBeam.threadColor.color',
            'orderRecipes',
            'orderRecipes.recipe.fiddles.thread',
            'orderRecipes.recipe.fiddles.color',
            'manufacturingCompany'
        ];

        $salesOrder->load('status');

        if ($salesOrder->status->code == MasterConstant::SO_MANUFACTURING) {
            $deliveredId = $this->masterRepository->findByCode(MasterConstant::SO_DELIVERED)->id;
            $relations['orderRecipes'] = function ($orderRecipes) use ($deliveredId) {
                /** @var Builder $orderRecipes */
                $orderRecipes->withCount([
                    'partialOrders as delivered_count' => function ($partialOrders) use (
                        $deliveredId
                    ) {
                        /** @var Builder $partialOrders */
                        $partialOrders->whereHas('delivery',
                            function ($delivery) use ($deliveredId) {
                                /** @var Builder $delivery */
                                $delivery->where('status_id', $deliveredId);
                            });
                    }
                ]);
            };
        }
        $salesOrder->load($relations);

        return $this->sendResponse($this->makeResource($salesOrder),
            __('messages.retrieved', ['module' => 'Sales Order']),
            HTTPCode::OK);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request) {
        $statuses = $this->totalMeterStatuses();
        try {
            $orders = $this->salesOrderRepository->getSalesOrderList($statuses[Master::SO_DELIVERED]['id'],
                $statuses[Master::SO_MANUFACTURING]['id'], $request->all());

            return $this->sendResponse($orders,
                __('messages.retrieved', ['module' => 'Sales Orders']),
                HTTPCode::OK);
        } catch (Exception $exception) {
            Log::error($exception);

            return $this->sendResponse(null, __('messages.something_wrong'),
                HTTPCode::UNPROCESSABLE_ENTITY, $exception);
        }
    }

    /**
     * @return mixed
     */
    private function totalMeterStatuses() {
        return $this->masterRepository->findWhereIn('code',
            [Master::SO_DELIVERED, Master::SO_MANUFACTURING], ['id', 'code'])
                                      ->keyBy('code')
                                      ->all();
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

    /**
     * @param AnalysisRequest $request
     * @return JsonResponse
     */
    public function threadAnalysis(AnalysisRequest $request) {
        $input = $request->all();
        $collection = collect($input['reports'])->keyBy('thread_color_id');
        try {
            $threadColors = $this->threadColorRepository->with([
                'availableStock',
                'thread' => function ($thread) {
                    /** @var Builder $thread */
                    $thread->select(['id', 'name', 'type_id', 'denier', 'company_name'])
                           ->with('type:id,name');
                },
                'color:id,name,code'
            ])->findWhereIn('id', $collection->pluck('thread_color_id')->toArray());

            foreach ($threadColors as &$threadColor) {
                $threadColor['available'] = ($threadColor->availableStock) ?
                    $threadColor->availableStock->available_qty : 0;
                $threadColor['used_in_design'] = $collection[$threadColor->id]['total_kg'];
                unset($threadColor->availableStock);
            }

            return $this->sendResponse($threadColors,
                __('messages.retrieved', ['module' => 'Stocks']),
                HTTPCode::OK);
        } catch (Exception $exception) {
            Log::error($exception);

            return $this->sendResponse(null, __('messages.something_wrong'),
                HTTPCode::UNPROCESSABLE_ENTITY, $exception);
        }
    }

    /**
     * @param SalesOrder $salesOrder
     * @return Response
     */
    public function exportSummary(SalesOrder $salesOrder) {
        return $this->renderSummary($salesOrder, $this->masterRepository)
                    ->download($salesOrder->order_no . ".pdf");
    }

    /**
     * @return JsonResponse
     */
    public function manufacturingCompanies() {
        try {
            $companies = (new CompanyRepository(new Container()))->all(['name', 'id']);

            return $this->sendResponse($companies,
                __('messages.retrieved', ['module' => 'Companies']),
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
        $statuses = $this->totalMeterStatuses();
        try {
            $sales = $this->salesOrderRepository->getSalesOrderList($statuses[Master::SO_DELIVERED]['id'],
                $statuses[Master::SO_MANUFACTURING]['id'], $request->all(), true);

            if (($sales = collect($sales->getData()->data))->isEmpty()) {
                return $this->sendResponse(null,
                    __('messages.can_not_export', ['module' => 'Sales orders']),
                    HTTPCode::OK);
            }

            return Excel::download(new ExportSalesOrder($sales), 'sales-orders.xlsx');
        } catch (Exception $exception) {
            Log::error($exception);

            return $this->sendResponse(null, __('messages.something_wrong'),
                HTTPCode::UNPROCESSABLE_ENTITY, $exception);
        }
    }

    /**
     * @param SalesOrder    $salesOrder
     * @param               $input
     * @return JsonResponse
     * @throws Exception
     */
    private function updateSOPENDINGStatus(SalesOrder $salesOrder, $input) {

        $status = $this->masterRepository->findByCode(MasterConstant::SO_PENDING);
        $salesOrder->orderStocks()->update(['status_id' => $status->id]);

        return $this->updateStatus($salesOrder, $status);

    }

    /**
     * @param SalesOrder $salesOrder
     * @param            $status
     * @return JsonResponse
     * @throws Exception
     */
    private function updateStatus(SalesOrder $salesOrder, $status) {
        try {
            $salesOrder->update(['status_id' => $status->id]);

            return $this->sendResponse($this->makeResource($salesOrder->load('status:id,name,code')),
                __('messages.updated', ['module' => 'Status']),
                HTTPCode::OK);
        } catch (Exception $exception) {
            Log::error($exception);

            throw $exception;
        }
    }

    /**
     * @param SalesOrder    $salesOrder
     * @param               $input
     * @return JsonResponse
     * @throws Exception
     */
    private function updateSOMANUFACTURINGStatus(SalesOrder $salesOrder, $input) {
        $status = $this->masterRepository->findByCode(MasterConstant::SO_MANUFACTURING);

        return $this->updateStatus($salesOrder, $status);

    }

    /**
     * @param SalesOrder    $salesOrder
     * @param               $input
     * @return JsonResponse
     * @throws Exception
     */
    private function updateSODELIVEREDStatus(SalesOrder $salesOrder, $input) {
        if (!$salesOrder->deliveries()->exists()) {
            return $this->sendResponse(null, __('messages.must_partial_delivery'),
                HTTPCode::UNPROCESSABLE_ENTITY);
        }
        $statusIds = $this->masterRepository->getIdsByCode
        ([MasterConstant::SO_DELIVERED, MasterConstant::SO_CANCELED]);


        $salesOrder->load([
            'deliveries' => function ($deliveries) use ($statusIds) {
                /** @var Builder $deliveries */
                $deliveries->whereNotIn('status_id', $statusIds);
            }
        ]);

        if ($salesOrder->deliveries->isNotEmpty()) {
            return $this->sendResponse(null, __('messages.complete_order'),
                HTTPCode::UNPROCESSABLE_ENTITY);
        }

        $status = $this->masterRepository->findByCode(MasterConstant::SO_DELIVERED);

        return $this->updateStatus($salesOrder, $status);

    }

    /**
     * @param SalesOrder    $salesOrder
     * @param               $input
     * @return JsonResponse
     * @throws Exception
     */
    private function updateSOCANCELEDStatus(SalesOrder $salesOrder, $input) {

        $status = $this->masterRepository->findByCode(MasterConstant::SO_CANCELED);

        $salesOrder->deliveries->each(function (Delivery $delivery) {
            $delivery->partialOrders()->delete();
        });

        $salesOrder->deliveries()->delete();

        $salesOrder->orderStocks()->delete();

        return $this->updateStatus($salesOrder, $status);

    }


}
