<?php

namespace App\Modules\Wastage\Http\Controllers;

use App\Constants\GenerateNumber;
use App\Constants\Master;
use App\Constants\Master as MasterConstant;
use App\Http\Controllers\Controller;
use App\Modules\Wastage\Http\Requests\CreateRequest;
use App\Modules\Wastage\Models\WastageOrder;
use App\Modules\Wastage\Repositories\WastageOrderRepository;
use App\Support\DestroyObject;
use App\Support\UniqueIdGenerator;
use DB;
use Exception;
use Knovators\Masters\Repository\MasterRepository;
use Knovators\Support\Helpers\HTTPCode;
use Log;

/**
 * Class WastageController
 * @package App\Modules\Wastage\Http\Controllers
 */
class WastageController extends Controller
{

    use DestroyObject, UniqueIdGenerator;

    protected $wastageOrderRepository;

    protected $masterRepository;

    /**
     * WastageController constructor
     * @param WastageOrderRepository $wastageOrderRepository
     * @param MasterRepository       $masterRepository
     */
    public function __construct(
        WastageOrderRepository $wastageOrderRepository,
        MasterRepository $masterRepository
    ) {
        $this->wastageOrderRepository = $wastageOrderRepository;
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
            $input['order_no'] = $this->generateUniqueId(GenerateNumber::WASTAGE);
            $input['status_id'] = $this->masterRepository->findByCode(MasterConstant::WASTAGE_PENDING)->id;
            $wastageOrder = $this->wastageOrderRepository->create($input);
//            $this->storeStockOrders($wastageOrder, $input);
            DB::commit();

            return $this->sendResponse($wastageOrder,
                __('messages.created', ['module' => 'Wastage Order']),
                HTTPCode::CREATED);
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error($exception);

            return $this->sendResponse(null, __('messages.something_wrong'),
                HTTPCode::UNPROCESSABLE_ENTITY, $exception);
        }
    }


}



