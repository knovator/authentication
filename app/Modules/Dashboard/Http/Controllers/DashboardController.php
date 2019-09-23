<?php

namespace App\Modules\Dashboard\Http\Controllers;

use App\Constants\Order as OrderConstant;
use App\Http\Controllers\Controller;
use App\Modules\Dashboard\Http\Requests\AnalysisRequest;
use App\Modules\Purchase\Repositories\PurchaseOrderRepository;
use App\Modules\Sales\Repositories\SalesOrderRepository;
use App\Modules\Wastage\Repositories\WastageOrderRepository;
use App\Modules\Yarn\Repositories\YarnOrderRepository;
use App\Repositories\MasterRepository;
use App\Support\DestroyObject;
use DB;
use Exception;
use Illuminate\Database\Eloquent\Collection;
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

    protected $yarnOrderRepository;

    protected $wastageOrderRepository;

    protected $purchaseOrderRepository;

    protected $masterRepository;


    /**
     * DashboardController constructor.
     * @param SalesOrderRepository    $salesOrderRepository
     * @param YarnOrderRepository     $yarnOrderRepository
     * @param WastageOrderRepository  $wastageOrderRepository
     * @param PurchaseOrderRepository $purchaseOrderRepository
     * @param MasterRepository        $masterRepository
     */
    public function __construct(
        SalesOrderRepository $salesOrderRepository,
        YarnOrderRepository $yarnOrderRepository,
        WastageOrderRepository $wastageOrderRepository,
        PurchaseOrderRepository $purchaseOrderRepository,
        MasterRepository $masterRepository
    ) {
        $this->salesOrderRepository = $salesOrderRepository;
        $this->yarnOrderRepository = $yarnOrderRepository;
        $this->wastageOrderRepository = $wastageOrderRepository;
        $this->purchaseOrderRepository = $purchaseOrderRepository;
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
            $orders[$orderType] = $this->commonOrderReport($input, 'salesOrderRepository', [
                MasterConstant::SO_PENDING,
                MasterConstant::SO_MANUFACTURING,
                MasterConstant::SO_DELIVERED,
            ], $statuses);
        }
        if (in_array($orderType = OrderConstant::YARN_ORDER, $input['types'])) {
            $orders[$orderType] = $this->commonOrderReport($input, 'yarnOrderRepository', [
                MasterConstant::SO_PENDING,
                MasterConstant::SO_DELIVERED,
            ], $statuses);
        }


        if (in_array($orderType = OrderConstant::WASTAGE_ORDER, $input['types'])) {
            $orders[$orderType] = $this->commonOrderReport($input, 'wastageOrderRepository', [
                MasterConstant::WASTAGE_PENDING,
                MasterConstant::WASTAGE_DELIVERED,
            ], $statuses);
        }


        if (in_array($orderType = OrderConstant::PURCHASE_ORDER, $input['types'])) {
            $orders[$orderType] = $this->commonOrderReport($input, 'purchaseOrderRepository', [
                MasterConstant::PO_PENDING,
                MasterConstant::PO_DELIVERED,
            ], $statuses);
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
     * @param                      $statuses
     * @return mixed
     */
    private function commonOrderReport($input, $repository, $statusCodes, $statuses) {
        /** @var \Illuminate\Support\Collection $statuses */
        $statusIds = $statuses->whereIn('code', $statusCodes)->pluck('id')->toArray();
        $totalOrders = $this->{$repository}->getOrderAnalysis($input, $statusIds);
        /** @var Collection $totalOrders */
        $orders['count']['total'] = $totalOrders->sum('total');
        foreach ($statusCodes as $statusCode) {
            $orders['count'][strtolower($statusCode)] = isset($totalOrders[$statuses[$statusCode]['id']]) ?
                $totalOrders[$statuses[$statusCode]['id']]['total'] : 0;
        }

        return $orders;
    }
}



