<?php

namespace App\Modules\Stock\Http\Controllers;

use App\Constants\Master;
use App\Http\Controllers\Controller;
use App\Modules\Stock\Models\Stock;
use App\Modules\Stock\Repositories\StockRepository;
use App\Modules\Thread\Constants\ThreadType;
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
        $input = $request->all();
        if (isset($input['type']) && $input['type'] == ThreadType::WARP) {
            $input['type_id'] = $this->masterRepository->findByCode(ThreadType::WARP)->id;
        }
        $stocks = $this->stockRepository->getStockOverview($this->statusFilters($input, true),
            $input);

        return $this->sendResponse($stocks,
            __('messages.retrieved', ['module' => 'Stocks']),
            HTTPCode::OK);
    }

    /**
     * @param      $input
     * @param bool $index
     * @return array
     */
    private function statusFilters($input, $index = false) {
        $statuses = $this->getMasterByCodes([
            Master::PO_PENDING,
            Master::PO_DELIVERED,
            Master::SO_PENDING,
            Master::WASTAGE_PENDING,
            Master::SO_MANUFACTURING,
            Master::SO_DELIVERED,
            Master::WASTAGE_DELIVERED,
        ]);

        if ($index) {

            $usedCount = Arr::only($statuses, [Master::PO_PENDING, Master::SO_MANUFACTURING]);

            $usedCount['so_pending'] = array_column(Arr::only($statuses,
                [Master::SO_PENDING, Master::WASTAGE_PENDING]), 'id');

            // warp type statuses
            if (isset($input['type_id']) || isset($input['is_demanded'])) {
                $usedCount['beam_statuses'] = Arr::only($statuses, [Master::SO_PENDING,
                                                        Master::SO_MANUFACTURING,Master::SO_DELIVERED]);

                $usedCount['so_delivered'] = array_column(Arr::only($statuses,
                    [Master::SO_DELIVERED, Master::WASTAGE_DELIVERED]), 'id');

            } else {
                $usedCount['remaining_count'] = array_column($statuses, 'id');
            }
        } else {
            $usedCount = Arr::except($statuses, [Master::PO_DELIVERED]);
        }


        $usedCount['available_count'] = array_column(Arr::only($statuses,
            Stock::AVAILABLE_STATUSES), 'id');


        return $usedCount;
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
     * @param Request     $request
     * @return JsonResponse
     */
    public function threadCount(ThreadColor $threadColor, Request $request) {
        $input = $request->all();
        try {
            $stock = $this->stockRepository->stockCount($threadColor->id,
                $this->statusFilters($input),$input);

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
