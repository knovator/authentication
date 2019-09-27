<?php

namespace App\Modules\Report\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Dashboard\Http\Requests\TopCustomerRequest;
use App\Modules\Sales\Repositories\SalesOrderRepository;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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

}



