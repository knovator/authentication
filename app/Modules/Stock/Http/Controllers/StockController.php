<?php

namespace App\Modules\Stock\Http\Controllers;

use App\Constants\Master;
use App\Http\Controllers\Controller;
use App\Modules\Thread\Repositories\ThreadColorRepository;
use App\Repositories\MasterRepository;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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

    /**
     * StockController constructor.
     * @param ThreadColorRepository $threadColorRepository
     * @param MasterRepository      $masterRepository
     */
    public function __construct(
        ThreadColorRepository $threadColorRepository,
        MasterRepository $masterRepository
    ) {
        $this->threadColorRepository = $threadColorRepository;
        $this->masterRepository = $masterRepository;
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
     * @param $code
     * @return integer
     */
    private function findMasterIdByCode($code) {
        return $this->masterRepository->findByCode($code)->id;
    }

}
