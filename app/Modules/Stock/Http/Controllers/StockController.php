<?php

namespace App\Modules\Stock\Http\Controllers;

use App\Constants\Master;
use App\Http\Controllers\Controller;
use App\Modules\Stock\Repositories\StockRepository;
use App\Modules\Thread\Models\Thread;
use App\Modules\Thread\Models\ThreadColor;
use App\Modules\Thread\Repositories\ThreadColorRepository;
use App\Repositories\MasterRepository;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Knovators\Support\Helpers\HTTPCode;
use Log;
use function Symfony\Component\Debug\Tests\testHeader;

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
            $poPendingId = $this->findMasterIdByCode(Master::PO_PENDING);
            $stocks = $this->threadColorRepository->getStockOverview($request->all(),
                $poPendingId);

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
            $poPendingId = $this->findMasterIdByCode(Master::PO_PENDING);
            $stocks = $this->threadColorRepository->stockCount($threadColor->id, $poPendingId);

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
        try {
            $reports = $this->stockRepository->getThreadOrderReport($threadColor);
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
     * @param $code
     * @return integer
     */
    private function findMasterIdByCode($code) {
        return $this->masterRepository->findByCode($code)->id;
    }

}
