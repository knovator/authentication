<?php

namespace App\Modules\Stock\Http\Controllers;

use App\Constants\Master;
use App\Http\Controllers\Controller;
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
     */
    public function index(Request $request) {
        try {
            $statusIds = $this->masterRepository->findWhereIn('code',
                [Master::PO_CANCELED, Master::PO_PENDING, Master::SO_CANCELED])->pluck('id')
                                                ->toArray();

            $stocks = $this->threadColorRepository->getStockOverview($statusIds);

            return $this->sendResponse($stocks,
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
    public function threadCount(ThreadColor $threadColor) {
        try {
            $statusIds = $this->masterRepository->findWhereIn('code',
                [Master::PO_CANCELED, Master::PO_PENDING, Master::SO_CANCELED])->pluck('id')
                                                ->toArray();

            $stocks = $this->threadColorRepository->stockCount($threadColor->id, $statusIds);

            return $this->sendResponse($stocks,
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
        $statuses = $this->getMasterByCodes();
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


    /**
     * @return mixed
     */
    private function getMasterByCodes() {
        return $this->masterRepository->findWhereIn('code',
            [
                // Po statuses
                Master::PO_PENDING,
                Master::PO_DELIVERED,
                // So statuses
                Master::SO_PENDING,
                Master::SO_MANUFACTURING,
                Master::SO_DELIVERED,
                // Wo statuses
                Master::WASTAGE_PENDING,
                Master::WASTAGE_DELIVERED,
                // canceled statuses
                Master::PO_CANCELED,
                Master::SO_CANCELED,
            ], ['id', 'code'])->keyBy('code')->toArray();
    }


    /**
     * @param $code
     * @return integer
     */
    private function findMasterIdByCode($code) {
        return $this->masterRepository->findByCode($code)->id;
    }

}
