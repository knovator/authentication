<?php

namespace App\Modules\Report\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Dashboard\Http\Requests\TopCustomerRequest;
use App\Modules\Sales\Repositories\SalesOrderRepository;
use Exception;
use Illuminate\Http\JsonResponse;
use Knovators\Support\Helpers\HTTPCode;
use Log;

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
     * @param TopCustomerRequest $request
     * @return JsonResponse
     */
    public function topCustomerList(TopCustomerRequest $request) {
        $input = $request->all();
        try {
            $customers = $this->salesOrderRepository->topCustomerReport($input, false);

            return $this->sendResponse($customers,
                __('messages.retrieved', ['module' => 'Customers']),
                HTTPCode::OK);
        } catch (Exception $exception) {
            Log::error($exception);

            return $this->sendResponse(null, __('messages.something_wrong'),
                HTTPCode::UNPROCESSABLE_ENTITY, $exception);
        }
    }
}



