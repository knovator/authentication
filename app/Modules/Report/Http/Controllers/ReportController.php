<?php

namespace App\Modules\Report\Http\Controllers;

use App\Constants\Master;
use App\Constants\Master as MasterConstant;
use App\Constants\Overview;
use App\Http\Controllers\Controller;
use App\Modules\Purchase\Repositories\PurchaseOrderRepository;
use App\Modules\Report\Http\Exports\CustomerExport;
use App\Modules\Report\Http\Exports\ThreadExport;
use App\Modules\Report\Http\Requests\OverviewRequest;
use App\Modules\Sales\Repositories\SalesOrderRepository;
use App\Modules\Stock\Repositories\StockRepository;
use App\Modules\Thread\Repositories\ThreadColorRepository;
use App\Modules\Wastage\Repositories\WastageOrderRepository;
use App\Modules\Yarn\Repositories\YarnOrderRepository;
use App\Repositories\MasterRepository;
use DB;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Knovators\Support\Helpers\HTTPCode;
use Log;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Class ReportController
 * @package App\Modules\Report\Http\Controllers
 */
class ReportController extends Controller
{

    protected $salesOrderRepository;

    protected $stockRepository;

    protected $masterRepository;

    protected $threadColorRepository;

    protected $purchaseOrderRepository;

    protected $yarnOrderRepository;

    protected $wastageOrderRepository;


    /**
     * ReportController constructor.
     * @param SalesOrderRepository    $salesOrderRepository
     * @param StockRepository         $stockRepository
     * @param MasterRepository        $masterRepository
     * @param ThreadColorRepository   $threadColorRepository
     * @param PurchaseOrderRepository $purchaseOrderRepository
     * @param YarnOrderRepository     $yarnOrderRepository
     * @param WastageOrderRepository  $wastageOrderRepository
     */
    public function __construct(
        SalesOrderRepository $salesOrderRepository,
        StockRepository $stockRepository,
        MasterRepository $masterRepository,
        ThreadColorRepository $threadColorRepository,
        PurchaseOrderRepository $purchaseOrderRepository,
        YarnOrderRepository $yarnOrderRepository,
        WastageOrderRepository $wastageOrderRepository
    ) {
        $this->salesOrderRepository = $salesOrderRepository;
        $this->stockRepository = $stockRepository;
        $this->masterRepository = $masterRepository;
        $this->threadColorRepository = $threadColorRepository;
        $this->purchaseOrderRepository = $purchaseOrderRepository;
        $this->yarnOrderRepository = $yarnOrderRepository;
        $this->wastageOrderRepository = $wastageOrderRepository;
    }

    /**
     * @param Request $request
     * @return JsonResponse|BinaryFileResponse
     */
    public function topCustomerExport(Request $request) {
        $input = $request->all();
        $canceledId = $this->masterRepository->findByCode(MasterConstant::SO_CANCELED)->id;
        $input['type'] = 'export';
        try {
            $orders = $this->salesOrderRepository->topCustomerReport($input, $canceledId);
            if (($orders = collect($orders))->isEmpty()) {
                return $this->sendResponse(null,
                    __('messages.can_not_export', ['module' => 'Customers']),
                    HTTPCode::OK);
            }

            return Excel::download(new CustomerExport($orders), 'top-customers.xlsx');
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
    public function leastUsedThreadExport(Request $request) {
        $input = $request->all();
        try {
            $soDeliveredId = $this->masterRepository->findByCode(Master::SO_DELIVERED)->id;
            $threads = $this->threadColorRepository->leastUsedThreads($input, $soDeliveredId, true);

            if (($threads = collect($threads))->isEmpty()) {
                return $this->sendResponse(null,
                    __('messages.can_not_export', ['module' => 'Threads']),
                    HTTPCode::OK);
            }

            return Excel::download(new ThreadExport($threads), 'threads.xlsx');
        } catch (Exception $exception) {
            Log::error($exception);

            return $this->sendResponse(null, __('messages.something_wrong'),
                HTTPCode::UNPROCESSABLE_ENTITY, $exception);
        }
    }


    /**
     * @param OverviewRequest $request
     * @return JsonResponse
     */
    public function orderViewReport(OverviewRequest $request) {
        $input = $request->all();
        try {
//            $orders['fabric'] = $this->salesOrderRepository->getReportList($input, 'total_meters');
            $orders['purchase'] = $this->purchaseOrderRepository->getReportList($input, 'total_kg');
//            $orders['yarn'] = $this->yarnOrderRepository->getReportList($input, 'total_kg');
//            $orders['wastage'] = $this->wastageOrderRepository->getReportList($input,
//                'total_meters');

            return $this->sendResponse($this->generateDateRange($orders, $input),
                __('messages.retrieved', ['module' => 'Overview']),
                HTTPCode::OK);
        } catch (Exception $exception) {
            Log::error($exception);

            return $this->sendResponse(null, __('messages.something_wrong'),
                HTTPCode::UNPROCESSABLE_ENTITY, $exception);
        }
    }

    /**
     * @param $orders
     * @param $input
     * @return array
     */
    private function generateDateRange($orders, $input) {
        $type = Overview::CHART_TYPES[$input['group']];
        $from = Carbon::parse($input['date_range']['start_date']);
        $to = Carbon::parse($input['date_range']['end_date']);
        $dates = [];
        for ($date = $from; $date->lte($to); $from->{'startOf' . $type}()->{'add' . $type}()) {
            $startDate = $date->format('Y-m-d');
            $year = $date->year;
            $dateInt = (($type == 'month' || $type == 'week') ? ($date->{$type} . '-' . $year) :
                (($type == 'day') ? $startDate : $year));
            $endDate = $date->{'endOf' . $type}();
            /** @var Carbon $endDate */
            if ($endDate->gt($to)) {
                $endDate = $to;
            }
//            $this->createDateForParticularOrder($dates, 'fabric', $orders['fabric'], $dateInt,
//                $startDate, $endDate, 'total_meters');
            $this->createDateForParticularOrder($dates, 'purchase', $orders['purchase'], $dateInt,
                $startDate, $endDate, 'total_kg');
//            $this->createDateForParticularOrder($dates, 'yarn', $orders['yarn'], $dateInt,
//                $startDate, $endDate, 'total_kg');
//            $this->createDateForParticularOrder($dates, 'wastage', $orders['wastage'], $dateInt,
//                $startDate, $endDate, 'total_meters');
        }

        return $dates;

    }

    /**
     * @param        $dates
     * @param        $type
     * @param        $orders
     * @param        $dateInt
     * @param        $startDate
     * @param Carbon $endDate
     * @param        $quantityType
     */
    private function createDateForParticularOrder(
        &$dates,
        $type,
        $orders,
        $dateInt,
        $startDate,
        Carbon $endDate,
        $quantityType
    ) {
        if (isset($orders[$dateInt])) {
            $order = $orders[$dateInt];
            $dates[$type][] = $this->formatDateResponse($startDate,
                $endDate->format('Y-m-d'),
                $order->total_orders, $order->{$quantityType}, $quantityType);
        } else {
            $dates[$type][] = $this->formatDateResponse($startDate,
                $endDate->format('Y-m-d'),
                0, 0, $quantityType);
        }
    }

    /**
     * @param $startDate
     * @param $endDate
     * @param $totalOrders
     * @param $totalQuantity
     * @param $quantityType
     * @return array
     */
    private function formatDateResponse(
        $startDate,
        $endDate,
        $totalOrders,
        $totalQuantity,
        $quantityType
    ) {
        return [
            [
                'start_date'  => $startDate,
                'end_date'    => $endDate,
                $quantityType => $totalQuantity
            ],
            $totalOrders
        ];
    }


}



