<?php

namespace App\Modules\Report\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Report\Http\Exports\CustomerExport;
use App\Modules\Sales\Repositories\SalesOrderRepository;
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


    /**
     * ReportController constructor.
     * @param SalesOrderRepository $salesOrderRepository
     */
    public function __construct(
        SalesOrderRepository $salesOrderRepository
    ) {
        $this->salesOrderRepository = $salesOrderRepository;
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

}



