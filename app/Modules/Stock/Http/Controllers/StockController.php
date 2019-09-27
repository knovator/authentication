<?php

namespace App\Modules\Stock\Http\Controllers;

use App\Constants\Master;
use App\Http\Controllers\Controller;
use App\Modules\Stock\Models\Stock;
use App\Modules\Stock\Repositories\StockRepository;
use App\Modules\Thread\Models\ThreadColor;
use App\Modules\Thread\Repositories\ThreadColorRepository;
use App\Repositories\MasterRepository;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Arr;
use Knovators\Support\Helpers\HTTPCode;
use Log;

/**
 * Class StockController
 * @package App\Modules\Stock\Http\Controllers
 */
class StockController extends Controller
{

    protected $threadColorRepository;

    protected $masterRepository;

    protected $stockRepository;

    /**
     * StockController constructor.
     * @param ThreadColorRepository $threadColorRepository
     * @param StockRepository       $stockRepository
     * @param MasterRepository      $masterRepository
     */
    public function __construct(
        ThreadColorRepository $threadColorRepository,
        StockRepository $stockRepository,
        MasterRepository $masterRepository
    ) {
        $this->threadColorRepository = $threadColorRepository;
        $this->masterRepository = $masterRepository;
        $this->stockRepository = $stockRepository;
    }


    /**
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function index(Request $request) {
        $filter = $this->statusFilters();
        $usedCount = $filter['userCount'];
        $usedCount['remaining_count'] = array_column([
            $filter['statuses'][Master::PO_DELIVERED],
            $filter['statuses'][Master::SO_MANUFACTURING],
            $filter['statuses'][Master::SO_DELIVERED],
            $filter['statuses'][Master::WASTAGE_DELIVERED],
            $filter['statuses'][Master::PO_PENDING],
            $filter['statuses'][Master::SO_PENDING],
        ], 'id');

        $stocks = $this->stockRepository->getStockOverview($usedCount);

        return $this->sendResponse($stocks,
            __('messages.retrieved', ['module' => 'Stocks']),
            HTTPCode::OK);
    }

    /**
     * @return array
     */
    private function statusFilters() {
        $statuses = $this->getMasterByCodes([
            Master::PO_PENDING,
            Master::SO_PENDING,
            Master::SO_MANUFACTURING,
            Master::SO_DELIVERED,
            Master::PO_DELIVERED,
            Master::WASTAGE_PENDING,
            Master::WASTAGE_DELIVERED,
        ]);
        $usedCount = Arr::except($statuses, [Master::PO_DELIVERED]);

        $usedCount['available_count'] = collect($statuses)
            ->whereIn('code', Stock::AVAILABLE_STATUSES)->pluck('id')->toArray();


        return ['statuses' => $statuses, 'userCount' => $usedCount];
    }

    /**
     * @param $codes
     * @return mixed
     */
    private function getMasterByCodes($codes) {
        return $this->masterRepository->findWhereIn('code',
            $codes, ['id', 'code'])->keyBy('code')->toArray();
    }

    /**
     * @param ThreadColor $threadColor
     * @return JsonResponse
     */
    public function threadCount(ThreadColor $threadColor) {
        try {
            $stock = $this->stockRepository->stockCount($threadColor->id,
                $this->statusFilters()['userCount']);

            return $this->sendResponse($stock,
                __('messages.retrieved', ['module' => 'Stocks']),
                HTTPCode::OK);
        } catch (Exception $exception) {
            Log::error($exception);

            return $this->sendResponse(null, __('messages.something_wrong'),
                HTTPCode::UNPROCESSABLE_ENTITY, $exception);
        }
    }

    /**
     * @param ThreadColor $threadColor
     * @return JsonResponse
     */
    public function threadReport(ThreadColor $threadColor) {
        $statuses = $this->getMasterByCodes([
            Master::PO_PENDING,
            Master::PO_DELIVERED,
            Master::SO_PENDING,
            Master::SO_MANUFACTURING,
            Master::SO_DELIVERED,
            Master::WASTAGE_PENDING,
            Master::WASTAGE_DELIVERED,
            Master::PO_CANCELED,
            Master::SO_CANCELED,
        ]);
        $stockCountStatus = Arr::except($statuses, [Master::PO_CANCELED, Master::SO_CANCELED]);
        try {
            $reports = $this->stockRepository->getThreadOrderReport($threadColor,
                [$statuses[Master::SO_CANCELED]['id'], $statuses[Master::PO_CANCELED]['id']],
                $stockCountStatus);

            return $this->sendResponse($reports,
                __('messages.retrieved', ['module' => 'Stocks']),
                HTTPCode::OK);
        } catch (Exception $exception) {
            Log::error($exception);

            return $this->sendResponse(null, __('messages.something_wrong'),
                HTTPCode::UNPROCESSABLE_ENTITY, $exception);
        }
    }

}
