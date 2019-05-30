<?php

namespace App\Modules\Purchase\Http\Controllers;

use App\Constants\GenerateNumber;
use App\Http\Controllers\Controller;
use App\Modules\Purchase\Http\Requests\CreateRequest;
use App\Modules\Purchase\Repositories\PurchaseOrderRepository;
use App\Support\UniqueIdGenerator;
use DB;
use Exception;
use Knovators\Masters\Repository\MasterRepository;
use Knovators\Support\Helpers\HTTPCode;
use Knovators\Support\Traits\DestroyObject;
use Log;

/**
 * Class PurchaseController
 * @package App\Modules\Purchase\Http\Controllers
 */
class PurchaseController extends Controller
{

    use DestroyObject, UniqueIdGenerator;

    protected $purchaseOrderRepository;

    protected $masterRepository;

    /**
     * PurchaseController constructor
     * @param PurchaseOrderRepository $purchaseOrderRepository
     * @param MasterRepository        $masterRepository
     */
    public function __construct(
        PurchaseOrderRepository $purchaseOrderRepository,
        MasterRepository $masterRepository
    ) {
        $this->purchaseOrderRepository = $purchaseOrderRepository;
        $this->masterRepository = $masterRepository;
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

            $purchaseOrder = $this->purchaseOrderRepository->create($input);
            $purchaseOrder->threads()->createMany($input['threads_purchase_details']);
            DB::commit();

            return $this->sendResponse($purchaseOrder,
                __('messages.created', ['module' => 'Purchase']),
                HTTPCode::CREATED);
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error($exception);

            return $this->sendResponse(null, __('messages.something_wrong'),
                HTTPCode::UNPROCESSABLE_ENTITY, $exception);
        }
    }


}



