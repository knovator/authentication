<?php

namespace App\Modules\Yarn\Http\Controllers;

use App\Constants\GenerateNumber;
use App\Constants\Master as MasterConstant;
use App\Http\Controllers\Controller;
use App\Modules\Yarn\Http\Requests\CreateRequest;
use App\Modules\Yarn\Models\YarnOrder;
use App\Modules\Yarn\Repositories\YarnOrderRepository;
use App\Support\DestroyObject;
use App\Support\UniqueIdGenerator;
use DB;
use Exception;
use Knovators\Masters\Repository\MasterRepository;
use Knovators\Support\Helpers\HTTPCode;
use Log;

/**
 * Class YarnController
 * @package App\Modules\Yarn\Http\Controllers
 */
class YarnController extends Controller
{

    use DestroyObject, UniqueIdGenerator;

    protected $yarnOrderRepository;

    protected $masterRepository;

    /**
     * YarnController constructor
     * @param YarnOrderRepository $yarnOrderRepository
     * @param MasterRepository    $masterRepository
     */
    public function __construct(
        YarnOrderRepository $yarnOrderRepository,
        MasterRepository $masterRepository
    ) {
        $this->yarnOrderRepository = $yarnOrderRepository;
        $this->masterRepository = $masterRepository;
    }

    /**
     * @param CreateRequest $request
     * @return mixed
     * @throws Exception
     */
    public function store(CreateRequest $request) {
        $input = $request->all();
        try {
            DB::beginTransaction();
            $input['order_no'] = $this->generateUniqueId(GenerateNumber::YARN_SALES);
            $input['status_id'] = $this->masterRepository->findByCode(MasterConstant::SO_PENDING)->id;
            $yarnOrder = $this->yarnOrderRepository->create($input);
            /** @var YarnOrder $yarnOrder */
            $yarnOrder->threads()->createMany($input['threads']);
            $this->storeStockOrders($yarnOrder, $input);
            DB::commit();

            return $this->sendResponse($yarnOrder,
                __('messages.created', ['module' => 'Sales Order']),
                HTTPCode::CREATED);
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error($exception);

            return $this->sendResponse(null, __('messages.something_wrong'),
                HTTPCode::UNPROCESSABLE_ENTITY, $exception);
        }
    }

    /**
     * @param $yarnOrder
     * @param $input
     */
    private function storeStockOrders(YarnOrder $yarnOrder, $input) {
        $stockItems = [];
        $yarnOrder->load('threads');
        foreach ($yarnOrder->threads as $key => $purchasedThread) {
            $stockItems[$key] = [
                'product_id'   => $purchasedThread->thread_color_id,
                'product_type' => 'thread_color',
                'kg_qty'       => $purchasedThread->kg_qty,
                'status_id'    => $input['status_id'],
            ];
        }
        $yarnOrder->orderStocks()->createMany($stockItems);
    }

}



