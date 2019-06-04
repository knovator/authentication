<?php

namespace App\Modules\Sales\Http\Controllers;

use App\Constants\GenerateNumber;
use App\Http\Controllers\Controller;
use App\Modules\Sales\Http\Requests\CreateRequest;
use App\Modules\Sales\Models\SalesOrder;
use App\Modules\Sales\Repositories\SalesOrderRepository;
use App\Support\UniqueIdGenerator;
use DB;
use Exception;
use Knovators\Support\Helpers\HTTPCode;
use Knovators\Support\Traits\DestroyObject;
use Log;

/**
 * Class SalesController
 * @package App\Modules\Sales\Http\Controllers
 */
class SalesController extends Controller
{

    use DestroyObject, UniqueIdGenerator;

    protected $salesOrderRepository;

    /**
     * SalesController constructor.
     * @param SalesOrderRepository $salesOrderRepository
     */
    public function __construct(
        SalesOrderRepository $salesOrderRepository
    ) {
        $this->salesOrderRepository = $salesOrderRepository;
    }


    /**
     * @param CreateRequest $request
     * @return mixed
     * @throws Exception
     */
    public function store(CreateRequest $request) {
        $input = $request->all();
        dd($input);
        try {
            DB::beginTransaction();
            $input['order_no'] = $this->generateUniqueId(GenerateNumber::PURCHASE);
            $salesOrder = $this->salesOrderRepository->create($input);






            DB::commit();

            return $this->sendResponse($salesOrder,
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
     * @param SalesOrder $salesOrder
     * //     * @return SalesOrderResource
     */
//    private function makeResource($salesOrder) {
//        return new SalesOrderResource($salesOrder);
//    }
//
//

}
