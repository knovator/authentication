<?php

namespace App\Modules\Customer\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\PartiallyUpdateRequest;
use App\Modules\Customer\Http\Requests\CreateRequest;
use App\Modules\Customer\Http\Requests\LedgerRequest;
use App\Modules\Customer\Http\Requests\UpdateRequest;
use App\Modules\Customer\Http\Resources\Customer as CustomerResource;
use App\Modules\Customer\Models\Customer;
use App\Modules\Customer\Repositories\AgentRepository;
use App\Modules\Customer\Repositories\CustomerRepository;
use App\Modules\Purchase\Repositories\PurchaseOrderRepository;
use App\Modules\Sales\Repositories\SalesOrderRepository;
use App\Modules\Wastage\Repositories\WastageOrderRepository;
use App\Modules\Yarn\Repositories\YarnOrderRepository;
use Exception;
use Illuminate\Http\JsonResponse;
use Knovators\Support\Helpers\HTTPCode;
use App\Support\DestroyObject;
use Log;
use Prettus\Repository\Exceptions\RepositoryException;
use Prettus\Validator\Exceptions\ValidatorException;
use App\Constants\Customer as CustomerConstant;
use Str;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * Class CustomerController
 * @package App\Modules\Customer\Http\Controllers
 */
class CustomerController extends Controller
{

    use DestroyObject;

    protected $customerRepository;

    protected $purchaseOrderRepository;

    protected $salesOrderRepository;

    protected $yarnOrderRepository;

    protected $wastageOrderRepository;

    protected $agentRepository;

    /**
     * CustomerController constructor.
     * @param CustomerRepository      $customerRepository
     * @param PurchaseOrderRepository $purchaseOrderRepository
     * @param SalesOrderRepository    $salesOrderRepository
     * @param YarnOrderRepository     $yarnOrderRepository
     * @param WastageOrderRepository  $wastageOrderRepository
     * @param AgentRepository         $agentRepository
     */
    public function __construct(
        CustomerRepository $customerRepository,
        PurchaseOrderRepository $purchaseOrderRepository,
        SalesOrderRepository $salesOrderRepository,
        YarnOrderRepository $yarnOrderRepository,
        WastageOrderRepository $wastageOrderRepository,
        AgentRepository $agentRepository
    ) {
        $this->customerRepository = $customerRepository;
        $this->purchaseOrderRepository = $purchaseOrderRepository;
        $this->salesOrderRepository = $salesOrderRepository;
        $this->yarnOrderRepository = $yarnOrderRepository;
        $this->wastageOrderRepository = $wastageOrderRepository;
        $this->agentRepository = $agentRepository;
    }

    /**
     * @param CreateRequest $request
     * @return mixed
     * @throws Exception
     */
    public function store(CreateRequest $request) {
        $input = $request->all();
        try {
            $this->firstOrCreateAgent($input);
            $customer = $this->customerRepository->create($input);

            return $this->sendResponse($this->makeResource($customer->load(['state', 'agent'])),
                __('messages.created', ['module' => 'Customer']),
                HTTPCode::CREATED);
        } catch (Exception $exception) {
            Log::error($exception);

            return $this->sendResponse(null, __('messages.something_wrong'),
                HTTPCode::UNPROCESSABLE_ENTITY, $exception);
        }
    }


    /**
     * @param $input
     * @throws ValidatorException
     * @throws RepositoryException
     */
    private function firstOrCreateAgent(&$input) {
        $slug = str_replace(' ', '-', Str::lower($input['agent_name']));
        if (!($agent = $this->agentRepository->findBy('slug', $slug))) {
            $agent = $this->agentRepository->create([
                'name'           => $input['agent_name'],
                'slug'           => $slug,
                'contact_number' => $input['agent_number']
            ]);

        }
        $input['agent_id'] = $agent->id;
    }

    /**
     * @param Customer $customer
     * @return CustomerResource
     */
    private function makeResource($customer) {
        return new CustomerResource($customer);
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
            $this->firstOrCreateAgent($input);
            $customer->update($input);
            $customer->fresh();

            return $this->sendResponse($this->makeResource($customer->load(['state', 'agent'])),
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

        return $this->sendResponse($this->makeResource($customer->load(['state', 'agent'])),
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
                'salesOrders',
                'purchaseOrders',
                'yarnOrders',
                'wastageOrders'
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
        return $this->sendResponse($this->makeResource($customer->load(['state', 'agent'])),
            __('messages.retrieved', ['module' => 'Customer']),
            HTTPCode::OK);
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

    /**
     * @return JsonResponse
     */
    public function agents() {
        try {
            $agents = $this->agentRepository->all(['id', 'name', 'contact_number']);

            return $this->sendResponse($agents,
                __('messages.retrieved', ['module' => 'Agents']),
                HTTPCode::OK);
        } catch (Exception $exception) {
            Log::error($exception);

            return $this->sendResponse(null, __('messages.something_wrong'),
                HTTPCode::UNPROCESSABLE_ENTITY, $exception);
        }
    }


    /**
     * @param Customer      $customer
     * @param LedgerRequest $request
     * @return JsonResponse
     */
    public function ledgers(Customer $customer, LedgerRequest $request) {
        $input = $request->all();
        try {
            return $this->sendResponse($this->orderList($customer, $input),
                __('messages.retrieved', ['module' => 'Customer ledger']),
                HTTPCode::OK);
        } catch (Exception $exception) {
            Log::error($exception);

            return $this->sendResponse(null, __('messages.something_wrong'),
                HTTPCode::UNPROCESSABLE_ENTITY);
        }
    }


    /**
     * @param $customer
     * @param $input
     * @return array
     * @throws Exception
     */
    private function orderList($customer, $input) {
        switch ($input['order_type']) {
            case CustomerConstant::LEDGER_PURCHASE:
                $orders = $this->purchaseOrderRepository->customerOrders($customer->id, $input);
                break;

            case CustomerConstant::LEDGER_FABRIC:
                $orders = $this->salesOrderRepository->customerOrders($customer->id, $input);
                break;

            case CustomerConstant::LEDGER_YARN:
                $orders = $this->yarnOrderRepository->customerOrders($customer->id, $input);
                break;

            case CustomerConstant::LEDGER_WASTAGE:
                $orders = $this->wastageOrderRepository->customerOrders($customer->id, $input);
                break;

            default:
                throw new UnprocessableEntityHttpException('Invalid order type');

        }
        $orders = datatables()->of($orders)->make(true);

        return $orders;

    }


}



