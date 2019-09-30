<?php

namespace App\Modules\Report\Http\Controllers;

use App\Constants\Master;
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
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
        $input['type'] = 'export';
        try {
            $orders = $this->salesOrderRepository->topCustomerReport($input);
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
//            $result['fabric'] = $this->salesOrderRepository->getReportList($input, 'total_meters');
            $result['purchase'] = $this->purchaseOrderRepository->getReportList($input, 'total_kg');
//            $result['yarn'] = $this->yarnOrderRepository->getReportList($input, 'total_kg');
//            $result['wastage'] = $this->wastageOrderRepository->getReportList($input,
//                'total_meters');
            return $this->sendResponse($result,
                __('messages.retrieved', ['module' => 'Overview']),
                HTTPCode::OK);
        } catch (Exception $exception) {
            Log::error($exception);

            return $this->sendResponse(null, __('messages.something_wrong'),
                HTTPCode::UNPROCESSABLE_ENTITY, $exception);
        }
    }


}



