<?php

namespace App\Modules\Dashboard\Http\Controllers;

use App\Constants\Order as OrderConstant;
use App\Http\Controllers\Controller;
use App\Modules\Dashboard\Http\Requests\AnalysisRequest;
use App\Modules\Sales\Repositories\SalesOrderRepository;
use App\Repositories\MasterRepository;
use App\Support\DestroyObject;
use Exception;
use Illuminate\Http\JsonResponse;
use Knovators\Support\Helpers\HTTPCode;
use Log;
use App\Constants\Master as MasterConstant;

/**
 * Class DashboardController
 * @package App\Modules\Dashboard\Http\Controllers
 */
class DashboardController extends Controller
{

    use DestroyObject;

    protected $salesOrderRepository;

    protected $masterRepository;


    /**
     * DashboardController constructor.
     * @param SalesOrderRepository $salesOrderRepository
     * @param MasterRepository     $masterRepository
     */
    public function __construct(
        SalesOrderRepository $salesOrderRepository,
        MasterRepository $masterRepository
    ) {
        $this->salesOrderRepository = $salesOrderRepository;
        $this->masterRepository = $masterRepository;
    }

    /**
     * @param AnalysisRequest $request
     * @return JsonResponse
     */
    public function analysis(AnalysisRequest $request) {
        $input = $request->all();
        try {
            return $this->sendResponse($this->orderAnalysis($input),
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
    private function orderAnalysis($input) {
        $orders = [];
        $statuses = $this->orderStatuses();
        if (in_array($orderType = OrderConstant::FABRIC_ORDER, $input['types'])) {
            $orders = $this->salesOrderRepository->getOrderAnalysis($input, [
                $statuses[MasterConstant::SO_PENDING]['id'],
                $statuses[MasterConstant::SO_MANUFACTURING]['id'],
                $statuses[MasterConstant::SO_DELIVERED]['id'],
            ]);

//            $orders[$orderType]['total_order'] = $this->salesOrderRepository->count();
//            $orders[$orderType]['pending_order'] = '';
//            $orders[$orderType]['manufacturing_order'] = '';
//            $orders[$orderType]['delivered_order'] = '';
        }

        if (in_array($orderType = OrderConstant::YARN_ORDER, $types)) {
            $orders[$orderType] = '';
        }

        if (in_array($orderType = OrderConstant::PURCHASE_ORDER, $types)) {
            $orders[$orderType] = '';
        }

        if (in_array($orderType = OrderConstant::WASTAGE_ORDER, $types)) {
            $orders[$orderType] = '';
        }

        return $orders;

    }

    /**
     * @param $codes
     * @return mixed
     */
    private function orderStatuses() {
        $codes = [
            MasterConstant::PO_PENDING,
            MasterConstant::SO_PENDING,
            MasterConstant::SO_MANUFACTURING,
            MasterConstant::SO_DELIVERED,
            MasterConstant::PO_DELIVERED,
            MasterConstant::WASTAGE_PENDING,
            MasterConstant::WASTAGE_DELIVERED,
        ];

        return $this->masterRepository->findWhereIn('code',
            $codes, ['id', 'code'])->keyBy('code')->toArray();
    }
}



