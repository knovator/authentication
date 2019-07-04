<?php

namespace App\Modules\Stock\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Stock\Repositories\StockRepository;
use Exception;
use Illuminate\Http\JsonResponse;
use Knovators\Support\Helpers\HTTPCode;
use Log;

/**
 * Class StockController
 * @package App\Modules\Stock\Http\Controllers
 */
class StockController extends Controller
{


    protected $stockRepository;

    /**
     * StockController constructor.
     * @param StockRepository $stockRepository
     */
    public function __construct(StockRepository $stockRepository) {
        $this->stockRepository = $stockRepository;
    }




}
