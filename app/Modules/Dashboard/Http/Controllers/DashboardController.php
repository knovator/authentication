<?php

namespace App\Modules\Dashboard\Http\Controllers;


use App\Constants\Master as MasterConstant;
use App\Constants\Order as OrderConstant;
use App\Http\Controllers\Controller;
use App\Modules\Dashboard\Http\Requests\AnalysisRequest;
use App\Modules\Dashboard\Http\Requests\LeastThreadRequest;
use App\Modules\Dashboard\Http\Requests\TopCustomerRequest;
use App\Modules\Design\Repositories\DesignRepository;
use App\Modules\Purchase\Repositories\PurchaseOrderRepository;
use App\Modules\Sales\Repositories\SalesOrderRepository;
use App\Modules\Stock\Models\Stock;
use App\Modules\Stock\Repositories\StockRepository;
use App\Modules\Thread\Repositories\ThreadColorRepository;
use App\Modules\Wastage\Repositories\WastageOrderRepository;
use App\Modules\Yarn\Repositories\YarnOrderRepository;
use App\Repositories\MasterRepository;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Knovators\Support\Helpers\HTTPCode;
use Log;

/**
 * Class DashboardController
 * @package App\Modules\Dashboard\Http\Controllers
 */
class DashboardController extends Controller
{

    protected $salesOrderRepository;

    protected $yarnOrderRepository;

    protected $wastageOrderRepository;

    protected $purchaseOrderRepository;

    protected $threadColorRepository;

    protected $masterRepository;

    protected $designRepository;

    protected $stockRepository;


    /**
     * DashboardController constructor.
     * @param SalesOrderRepository    $salesOrderRepository
     * @param YarnOrderRepository     $yarnOrderRepository
     * @param WastageOrderRepository  $wastageOrderRepository
     * @param PurchaseOrderRepository $purchaseOrderRepository
     * @param ThreadColorRepository   $threadColorRepository
     * @param MasterRepository        $masterRepository
     * @param DesignRepository        $designRepository
     * @param StockRepository         $stockRepository
     */
    public function __construct(
        SalesOrderRepository $salesOrderRepository,
        YarnOrderRepository $yarnOrderRepository,
        WastageOrderRepository $wastageOrderRepository,
        PurchaseOrderRepository $purchaseOrderRepository,
        ThreadColorRepository $threadColorRepository,
        MasterRepository $masterRepository,
        DesignRepository $designRepository,
        StockRepository $stockRepository
    ) {
        $this->salesOrderRepository = $salesOrderRepository;
        $this->yarnOrderRepository = $yarnOrderRepository;
        $this->wastageOrderRepository = $wastageOrderRepository;
        $this->purchaseOrderRepository = $purchaseOrderRepository;
        $this->threadColorRepository = $threadColorRepository;
        $this->masterRepository = $masterRepository;
        $this->designRepository = $designRepository;
        $this->stockRepository = $stockRepository;
    }

    /**
     * @param AnalysisRequest $request
     * @return JsonResponse
     */
    public function orderAnalysis(AnalysisRequest $request) {
        $input = $request->all();
        try {
            return $this->sendResponse($this->orderTypeAnalysis($input),
                __('messages.retrieved', ['module' => 'Order analysis']),
                HTTPCode::OK);
        } catch (Exception $exception) {
            Log::error($exception);

            return $this->sendResponse(null, __('messages.something_wrong'),
                HTTPCode::UNPROCESSABLE_ENTITY, $exception);
        }
    }

    /**
     * @param $input
     * @return array
     */
    private function orderTypeAnalysis($input) {
        $this->financialYear($input);
        $statuses = $this->orderStatuses();
        $orders = [];

        if (in_array($orderType = OrderConstant::SALES_ORDER, $input['types'])) {
            $orders[$orderType] = $this->commonOrderReport($input,
                'salesOrderRepository', [
                    MasterConstant::SO_PENDING,
                    MasterConstant::SO_MANUFACTURING,
                    MasterConstant::SO_DELIVERED,
                ], $statuses);

        }
        if (in_array($orderType = OrderConstant::YARN_ORDER, $input['types'])) {
            $orders[$orderType] = $this->commonOrderReport($input,
                'yarnOrderRepository', [
                    MasterConstant::SO_PENDING,
                    MasterConstant::SO_DELIVERED,
                ], $statuses);
        }

        if (in_array($orderType = OrderConstant::WASTAGE_ORDER, $input['types'])) {
            $orders[$orderType] = $this->commonOrderReport($input,
                'wastageOrderRepository', [
                    MasterConstant::WASTAGE_PENDING,
                    MasterConstant::WASTAGE_DELIVERED,
                ], $statuses);
        }

        if (in_array($orderType = OrderConstant::PURCHASE_ORDER, $input['types'])) {
            $orders[$orderType] = $this->commonOrderReport($input,
                'purchaseOrderRepository', [
                    MasterConstant::PO_PENDING,
                    MasterConstant::PO_DELIVERED,
                ], $statuses);
        }

        return $orders;
    }

    /**
     * @param $input
     */
    private function financialYear(&$input) {
        $year = (date('Y') > 3) ? date('Y') + 1 : (int) date('Y');
        $input['startDate'] = ($year - 1) . '-04-01';
        $input['endDate'] = ($year) . '-03-31';
    }

    /**
     * @param $codes
     * @return mixed
     */
    private function orderStatuses() {
        $codes = [
            MasterConstant::PO_PENDING,
            MasterConstant::PO_DELIVERED,
            MasterConstant::SO_PENDING,
            MasterConstant::SO_MANUFACTURING,
            MasterConstant::SO_DELIVERED,
            MasterConstant::WASTAGE_PENDING,
            MasterConstant::WASTAGE_DELIVERED,
        ];

        return $this->masterRepository->findWhereIn('code',
            $codes, ['id', 'code'])->keyBy('code');
    }

    /**
     * @param                      $input
     * @param string               $repository
     * @param                      $statusCodes
     * @param                      $allStatuses
     * @return mixed
     */
    private function commonOrderReport($input, $repository, $statusCodes, $allStatuses) {
        /** @var Collection $allStatuses */
        $statuses = $allStatuses->whereIn('code', $statusCodes)->all();

        return $this->{$repository}->getOrderAnalysis($input, $statuses);
    }

    /**
     * @return JsonResponse
     */
    public function designAnalysis() {
        try {
            return $this->sendResponse($this->designRepository->designCount(),
                __('messages.retrieved', ['module' => 'Design analysis']),
                HTTPCode::OK);
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
    public function mostUsedDesign(Request $request) {
        $input = $request->all();
        try {
            $designs = $this->salesOrderRepository->mostUsedDesignReport($input);

            return $this->sendResponse($designs,
                __('messages.retrieved', ['module' => 'Design analysis']),
                HTTPCode::OK);
        } catch (Exception $exception) {
            Log::error($exception);

            return $this->sendResponse(null, __('messages.something_wrong'),
                HTTPCode::UNPROCESSABLE_ENTITY, $exception);
        }
    }

    /**
     * @param TopCustomerRequest $request
     * @return JsonResponse
     */
    public function topCustomerReport(TopCustomerRequest $request) {
        $input = $request->all();
        if ($input['api'] == 'dashboard' && $input['type'] == 'chart') {
            $this->financialYear($input);
        }
        $canceledId = $this->masterRepository->findByCode(MasterConstant::SO_CANCELED)->id;
        try {
            $customers = $this->salesOrderRepository->topCustomerReport($input, $canceledId);

            return $this->sendResponse($customers,
                __('messages.retrieved', ['module' => 'Customers']),
                HTTPCode::OK);
        } catch (Exception $exception) {
            Log::error($exception);

            return $this->sendResponse(null, __('messages.something_wrong'),
                HTTPCode::UNPROCESSABLE_ENTITY, $exception);
        }
    }

    /**
     * @param LeastThreadRequest $request
     * @return JsonResponse
     */
    public function leastUsedThreadChart(LeastThreadRequest $request) {
        $statuses = $this->statusFilters();

        $usedCount['available_count'] = collect($statuses)
            ->whereIn('code', Stock::AVAILABLE_STATUSES)->pluck('id')->toArray();

        try {
            $threads = $this->stockRepository->leastUsedThreads($statuses,$usedCount);

            return $this->sendResponse($threads,
                __('messages.retrieved', ['module' => 'Threads']),
                HTTPCode::OK);
        } catch (Exception $exception) {
            Log::error($exception);

            return $this->sendResponse(null, __('messages.something_wrong'),
                HTTPCode::UNPROCESSABLE_ENTITY, $exception);
        }
    }

    /**
     * @return array
     */
    private function statusFilters() {
        $statuses = $this->getMasterByCodes([
            MasterConstant::PO_DELIVERED,
            MasterConstant::SO_MANUFACTURING,
            MasterConstant::SO_DELIVERED,
            MasterConstant::WASTAGE_DELIVERED,
        ]);

        return $statuses;
    }


    /**
     * @param $codes
     * @return mixed
     */
    private function getMasterByCodes($codes) {
        return $this->masterRepository->findWhereIn('code',
            $codes, ['id', 'code'])->keyBy('code')->toArray();
    }


}



