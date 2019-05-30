<?php

namespace App\Modules\Customer\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\PartiallyUpdateRequest;
use App\Modules\Customer\Http\Requests\CreateRequest;
use App\Modules\Customer\Http\Requests\UpdateRequest;
use App\Modules\Customer\Http\Resources\Customer as CustomerResource;
use App\Modules\Customer\Models\Customer;
use App\Modules\Customer\Repositories\CustomerRepository;
use Exception;
use Illuminate\Http\JsonResponse;
use Knovators\Support\Helpers\HTTPCode;
use Knovators\Support\Traits\DestroyObject;
use Log;

/**
 * Class CustomerController
 * @package App\Modules\Customer\Http\Controllers
 */
class CustomerController extends Controller
{

    use DestroyObject;

    protected $customerRepository;

    /**
     * CustomerController constructor.
     * @param CustomerRepository $customerRepository
     */
    public function __construct(
        CustomerRepository $customerRepository
    ) {
        $this->customerRepository = $customerRepository;
    }

    /**
     * @param CreateRequest $request
     * @return mixed
     * @throws Exception
     */
    public function store(CreateRequest $request) {
        $input = $request->all();
        try {
            $customer = $this->customerRepository->create($input);

            return $this->sendResponse($this->makeResource($customer->load('state')),
                __('messages.created', ['module' => 'Customer']),
                HTTPCode::CREATED);
        } catch (Exception $exception) {
            Log::error($exception);

            return $this->sendResponse(null, __('messages.something_wrong'),
                HTTPCode::UNPROCESSABLE_ENTITY, $exception);
        }
    }


    /**
     * @param Customer      $customer
     * @param UpdateRequest $request
     * @return JsonResponse
     * @throws Exception
     */
    public function update(Customer $customer, UpdateRequest $request) {
        $input = $request->all();
        try {
            $customer->update($input);
            $customer->fresh();

            return $this->sendResponse($this->makeResource($customer->load('state')),
                __('messages.updated', ['module' => 'Customer']),
                HTTPCode::OK);
        } catch (Exception $exception) {
            Log::error($exception);

            return $this->sendResponse(null, __('messages.something_wrong'),
                HTTPCode::UNPROCESSABLE_ENTITY, $exception);
        }
    }

    /**
     * @param Customer               $customer
     * @param PartiallyUpdateRequest $request
     * @return JsonResponse
     */

    public function partiallyUpdate(Customer $customer, PartiallyUpdateRequest $request) {
        $customer->update($request->all());
        $customer->fresh();

        return $this->sendResponse($this->makeResource($customer->load('state')),
            __('messages.updated', ['module' => 'Customer']),
            HTTPCode::OK);
    }

    /**
     * @param Customer $customer
     * @return JsonResponse
     */
    public function destroy(Customer $customer) {
        try {
            // Customer associated relations
            $relations = [
                'salesOrders'
            ];

            return $this->destroyModelObject($relations, $customer, 'Customer');

        } catch (Exception $exception) {
            Log::error($exception);

            return $this->sendResponse(null, __('messages.something_wrong'),
                HTTPCode::UNPROCESSABLE_ENTITY, $exception);
        }
    }

    /**
     * @param Customer $customer
     * @return JsonResponse
     */
    public function show(Customer $customer) {
        return $this->sendResponse($this->makeResource($customer->load('state')),
            __('messages.retrieved', ['module' => 'Customer']),
            HTTPCode::OK);
    }


    /**
     * @param Customer $customer
     * @return CustomerResource
     */
    private function makeResource($customer) {
        return new CustomerResource($customer);
    }


    /**
     * @return JsonResponse
     */
    public function index() {
        try {
            $customers = $this->customerRepository->getCustomerList();

            return $this->sendResponse($customers,
                __('messages.retrieved', ['module' => 'Customers']),
                HTTPCode::OK);
        } catch (Exception $exception) {
            Log::error($exception);

            return $this->sendResponse(null, __('messages.something_wrong'),
                HTTPCode::UNPROCESSABLE_ENTITY);
        }
    }

}



