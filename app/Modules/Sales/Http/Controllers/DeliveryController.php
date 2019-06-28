<?php

namespace App\Modules\Sales\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Sales\Http\Requests\Delivery\CreateRequest;
use App\Modules\Sales\Models\Delivery;
use App\Modules\Sales\Models\SalesOrder;
use App\Modules\Sales\Repositories\DeliveryRepository;
use App\Repositories\MasterRepository;
use DB;
use Exception;
use Knovators\Support\Helpers\HTTPCode;
use Log;
use App\Constants\Master as MasterConstant;

/**
 * Class DeliveryController
 * @package App\Modules\Sales\Http\Controllers
 */
class DeliveryController extends Controller
{

    protected $deliveryRepository;

    protected $masterRepository;

    /**
     * DeliveryController constructor.
     * @param DeliveryRepository $deliveryRepository
     * @param MasterRepository   $masterRepository
     */
    public function __construct(
        DeliveryRepository $deliveryRepository,
        MasterRepository $masterRepository
    ) {
        $this->deliveryRepository = $deliveryRepository;
        $this->masterRepository = $masterRepository;
    }


    /**
     * @param SalesOrder    $salesOrder
     * @param CreateRequest $request
     * @return mixed
     */
    public function store(SalesOrder $salesOrder, CreateRequest $request) {
        $input = $request->all();
        $input['status_id'] = $this->masterRepository->findByCode(MasterConstant::SO_PENDING)->id;
        try {
            DB::beginTransaction();
            $delivery = $this->deliveryRepository->create($input);
            /** @var Delivery $delivery */
            $delivery->partialOrders()->createMany($input['orders']);
            DB::commit();

            return $this->sendResponse($delivery,
                __('messages.created', ['module' => 'Delivery']),
                HTTPCode::CREATED);
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error($exception);

            return $this->sendResponse(null, __('messages.something_wrong'),
                HTTPCode::UNPROCESSABLE_ENTITY, $exception);
        }
    }
}
