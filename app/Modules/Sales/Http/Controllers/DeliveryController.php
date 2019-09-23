<?php

namespace App\Modules\Sales\Http\Controllers;

use App\Constants\GenerateNumber;
use App\Constants\Master as MasterConstant;
use App\Http\Controllers\Controller;
use App\Models\Master;
use App\Modules\Design\Repositories\DesignDetailRepository;
use App\Modules\Machine\Repositories\MachineRepository;
use App\Modules\Sales\Http\Requests\Delivery\CreateRequest;
use App\Modules\Sales\Http\Requests\Delivery\StatusRequest;
use App\Modules\Sales\Http\Requests\Delivery\UpdateRequest;
use App\Modules\Sales\Http\Resources\Delivery as DeliveryResource;
use App\Modules\Sales\Models\Delivery;
use App\Modules\Sales\Models\RecipePartialOrder;
use App\Modules\Sales\Models\SalesOrder;
use App\Modules\Sales\Repositories\DeliveryRepository;
use App\Modules\Sales\Repositories\SalesRecipeRepository;
use App\Modules\Stock\Repositories\StockRepository;
use App\Modules\Thread\Constants\ThreadType;
use App\Modules\Thread\Repositories\ThreadColorRepository;
use App\Repositories\MasterRepository;
use App\Support\DestroyObject;
use App\Support\Formula;
use App\Support\UniqueIdGenerator;
use Barryvdh\Snappy\Facades\SnappyPdf;
use Barryvdh\Snappy\ImageWrapper;
use DB;
use Exception;
use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Knovators\Support\Helpers\HTTPCode;
use Log;
use Prettus\Repository\Exceptions\RepositoryException;
use Str;
use Symfony\Component\HttpFoundation\Response;

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

    protected $threadColorRepository;

    /**
     * DeliveryController constructor.
     * @param DeliveryRepository     $deliveryRepository
     * @param MasterRepository       $masterRepository
     * @param SalesRecipeRepository  $orderRecipeRepository
     * @param StockRepository        $stockRepository
     * @param DesignDetailRepository $designDetailRepository
     * @param ThreadColorRepository  $threadColorRepository
     */
    public function __construct(
        DeliveryRepository $deliveryRepository,
        MasterRepository $masterRepository,
        SalesRecipeRepository $orderRecipeRepository,
        StockRepository $stockRepository,
        DesignDetailRepository $designDetailRepository,
        ThreadColorRepository $threadColorRepository
    ) {
        $this->deliveryRepository = $deliveryRepository;
        $this->masterRepository = $masterRepository;
        $this->orderRecipeRepository = $orderRecipeRepository;
        $this->stockRepository = $stockRepository;
        $this->designDetailRepository = $designDetailRepository;
        $this->threadColorRepository = $threadColorRepository;
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
            $delivery = $salesOrder->deliveries()->create($input);
            /** @var Delivery $delivery */
            $delivery->partialOrders()->createMany($input['orders']);
            $this->storeStockDetails($salesOrder, $input['status_id']);
            DB::commit();

            return $this->sendResponse($delivery->load($this->deliveryRepository->commonRelations()),
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
            if ($remainingMeters > 0) {
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
            'kg_qty'          => -1 * $kgQty,
        ];

        if ($partialOrder) {
            /** @var RecipePartialOrder $partialOrder */
            $stock['partial_order_id'] = $partialOrder->id;
            $stock['partial_order_type'] = 'sales_partial';
        }

        return $stock;
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

            return $this->sendResponse($delivery->fresh($this->deliveryRepository->commonRelations()),
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


    /**
     * @param SalesOrder $salesOrder
     * @return JsonResponse
     */
    public function index(SalesOrder $salesOrder) {
        try {
            $deliveries = $this->deliveryRepository->getDeliveryList($salesOrder->id);

            return $this->sendResponse($deliveries,
                __('messages.retrieved', ['module' => 'Delivery']),
                HTTPCode::OK);
        } catch (Exception $exception) {
            Log::error($exception);

            return $this->sendResponse(null, __('messages.something_wrong'),
                HTTPCode::UNPROCESSABLE_ENTITY, $exception);
        }
    }


    /**
     * @param StatusRequest $request
     * @return JsonResponse
     * @throws RepositoryException
     */
    public function changeStatus(StatusRequest $request) {
        $status = $request->get('code');
        $method = 'update' . Str::studly($status) . 'Status';
        $delivery = $this->deliveryRepository->find($request->get('delivery_id'));
        if (method_exists($this, $method)) {
            return $this->{$method}($delivery, $request->all());
        }
        Log::error('Unable to find status method: ' . $status);

        return $this->sendResponse(null, __('messages.something_wrong'),
            HTTPCode::UNPROCESSABLE_ENTITY);

    }

    /**
     * @param SalesOrder $salesOrder
     * @param Delivery   $delivery
     * @return Response
     */
    public function exportManufacturing(SalesOrder $salesOrder, Delivery $delivery) {
        $salesOrder->load([
            'design.detail',
            'design.fiddlePicks',
            'customer',
            'designBeam.threadColor.thread',
            'designBeam.threadColor.color:id,name,code',
        ]);
        $machineRepo = new MachineRepository(new Container());
        $machines = $machineRepo->manufacturingReceipts($delivery->id);
        $pdf = SnappyPdf::loadView('receipts.sales-orders.manufacturing.manufacturing',
            compact('machines', 'salesOrder', 'delivery'));

        /** @var ImageWrapper $pdf */
        return $pdf->download($delivery->delivery_no . '-manufacturing' . ".pdf");
    }

    /**
     * @param SalesOrder $salesOrder
     * @param Delivery   $delivery
     * @return Response
     */
    public function exportAccounting(SalesOrder $salesOrder, Delivery $delivery) {
        $salesOrder->load([
            'manufacturingCompany',
            'design.mainImage.file',
            'customer.state'
        ]);
        $delivery->load([
            'partialOrders' => function ($partialOrders) {
                /** @var Builder $partialOrders */
                $partialOrders->with([
                    'orderRecipe.recipe.fiddles' => function ($fiddles) {
                        $fiddles->where('recipes_fiddles.fiddle_no', '=', 1)->with('color');
                    }
                ])->orderByDesc('id');
            }
        ]);
        if ($delivery->partialOrders->isEmpty()) {
            return $this->sendResponse(null, __('messages.partial_order_not_present'),
                HTTPCode::UNPROCESSABLE_ENTITY);
        }

        $pdf = SnappyPdf::loadView('receipts.sales-orders.accounting.accounting',
            compact('salesOrder', 'delivery'));

        /** @var ImageWrapper $pdf */
        return $pdf->download($delivery->delivery_no . '-accounting' . ".pdf");
    }

    /**
     * @param Delivery $delivery
     * @param          $input
     * @return JsonResponse
     */
    private function updateSOPENDINGStatus(Delivery $delivery, $input) {
        try {
            return $this->updateStatus($delivery, MasterConstant::SO_PENDING, $input);
        } catch (Exception $exception) {
            Log::error($exception);

            return $this->sendResponse(null, __('messages.something_wrong'),
                HTTPCode::UNPROCESSABLE_ENTITY, $exception);
        }


    }

    /**
     * @param Delivery $delivery
     * @param          $code
     * @param          $input
     * @return JsonResponse
     * @throws Exception
     */
    private function updateStatus(Delivery $delivery, $code, $input) {
        $status = $this->findMasterIdByCode($code);
        /** @var Master $status */
        $input['status_id'] = $status->id;
        try {
            DB::beginTransaction();
            if ($code !== MasterConstant::SO_COMPLETED) {
                $delivery->orderStocks()->update(['status_id' => $status->id]);
            }
            $delivery->update($input);
            DB::commit();

            return $this->sendResponse($delivery->fresh(['status:id,name,code']),
                __('messages.updated', ['module' => 'Status']),
                HTTPCode::OK);
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error($exception);
            throw $exception;
        }
    }

    /**
     * @param $code
     * @return integer
     */
    private function findMasterIdByCode($code) {
        return $this->masterRepository->findByCode($code);
    }

    /**
     * @param Delivery $delivery
     * @param          $input
     * @return JsonResponse
     * @throws Exception
     */
    private function updateSOCANCELEDStatus(Delivery $delivery, $input) {
        /** @var Master $status */
        $status = $this->findMasterIdByCode(MasterConstant::SO_CANCELED);
        try {

            DB::beginTransaction();
            $delivery->partialOrders()->delete();
            $delivery->update(['status_id' => $status->id]);
            $delivery->load('salesOrder');
            $this->storeStockDetails($delivery->salesOrder,
                $this->findMasterIdByCode(MasterConstant::SO_PENDING)->id);

            DB::commit();

            return $this->sendResponse($delivery->fresh(['status:id,name,code']),
                __('messages.updated', ['module' => 'Status']),
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
     * @param          $input
     * @return JsonResponse
     */
    private function updateSODELIVEREDStatus(Delivery $delivery, $input) {
        try {
            return $this->updateStatus($delivery, MasterConstant::SO_DELIVERED, $input);
        } catch (Exception $exception) {
            Log::error($exception);

            return $this->sendResponse(null, __('messages.something_wrong'),
                HTTPCode::UNPROCESSABLE_ENTITY, $exception);
        }

    }


    /**
     * @param Delivery $delivery
     * @param          $input
     * @return JsonResponse
     */
    private function updateSoCompletedStatus(Delivery $delivery, $input) {
        try {
            return $this->updateStatus($delivery, MasterConstant::SO_COMPLETED, $input);
        } catch (Exception $exception) {
            Log::error($exception);

            return $this->sendResponse(null, __('messages.something_wrong'),
                HTTPCode::UNPROCESSABLE_ENTITY, $exception);
        }

    }

    /**
     * @param Delivery $delivery
     * @param          $input
     * @return JsonResponse
     */
    private function updateSOMANUFACTURINGStatus(Delivery $delivery, $input) {
        try {
            $deliveryStocks = $this->deliveryRepository->usedStocks($delivery);
            $threadColors = $this->threadColorRepository->findWithAvailableQty(array_keys($deliveryStocks));
            foreach ($threadColors as $thread) {
                if (!is_null($thread->availableStock)) {
                    $newQty = $thread->availableStock->available_qty +
                        $deliveryStocks[$thread->id]['used_stock'];
                } else {
                    $newQty = $deliveryStocks[$thread->id]['used_stock'];
                }
                if ($newQty < 0) {
                    $newQty = ceil(str_replace('-', '', $newQty));

                    return $this->sendResponse(null,
                        "{$thread->thread->denier}-{$thread->thread->name}-{$thread->color->name} ({$newQty}KG) is not available for manufacturing.",
                        HTTPCode::UNPROCESSABLE_ENTITY);
                }
            }
            $this->storeMachineDetails($delivery);

            return $this->updateStatus($delivery, MasterConstant::SO_MANUFACTURING, $input);
        } catch (Exception $exception) {
            Log::error($exception);

            return $this->sendResponse(null, __('messages.something_wrong'),
                HTTPCode::UNPROCESSABLE_ENTITY, $exception);
        }

    }


    /**
     * @param $delivery
     */
    private function storeMachineDetails(Delivery $delivery) {
        $delivery->load(['partialOrders.machine:id,name,reed,panno', 'salesOrder:id']);
        $machines = [];
        foreach ($delivery->partialOrders as $partialOrder) {
            /** @var RecipePartialOrder $partialOrder */
            $machines[] = [
                'name'             => $partialOrder->machine->name,
                'reed'             => $partialOrder->machine->reed,
                'panno'            => $partialOrder->machine->panno,
                'machine_id'       => $partialOrder->machine_id,
                'partial_order_id' => $partialOrder->id,
            ];
        }
        /** @var SalesOrder $delivery ->salesOrder */
        $delivery->salesOrder->assignMachines()->createMany($machines);
    }


}
