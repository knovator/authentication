<?php

namespace App\Modules\Sales\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Sales\Http\Delivery\Requests\CreateRequest;
use App\Modules\Sales\Repositories\DeliveryRepository;
use App\Modules\Sales\Repositories\SalesOrderRepository;
use DB;
use Exception;
use Knovators\Support\Helpers\HTTPCode;
use Log;

/**
 * Class DeliveryController
 * @package App\Modules\Sales\Http\Controllers
 */
class DeliveryController extends Controller
{

    protected $deliveryRepository;

    /**
     * DeliveryController constructor.
     * @param DeliveryRepository $deliveryRepository
     */
    public function __construct(
        DeliveryRepository $deliveryRepository
    ) {
        $this->deliveryRepository = $deliveryRepository;
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
            $this->deliveryRepository->create($input);

            DB::commit();

            return $this->sendResponse(null,
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
