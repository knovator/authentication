<?php

namespace App\Modules\Customer\Http\Controllers;

use App\Constants\Master;
use App\Http\Controllers\Controller;
use App\Http\Requests\PartiallyUpdateRequest;
use App\Modules\Customer\Http\Exports\Ledger as ExportLedger;
use App\Modules\Customer\Http\Requests\CreateRequest;
use App\Modules\Customer\Http\Requests\LedgerRequest;
use App\Modules\Customer\Http\Requests\UpdateRequest;
use App\Modules\Customer\Http\Resources\Customer as CustomerResource;
use App\Modules\Customer\Models\Customer;
use App\Modules\Customer\Repositories\AgentRepository;
use App\Modules\Customer\Repositories\CustomerRepository;
use App\Modules\Purchase\Repositories\PurchaseOrderRepository;
use App\Modules\Sales\Repositories\SalesOrderRepository;
use App\Modules\Stock\Repositories\StockRepository;
use App\Modules\Wastage\Repositories\WastageOrderRepository;
use App\Modules\Yarn\Repositories\YarnOrderRepository;
use App\Repositories\MasterRepository;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Knovators\Support\Helpers\HTTPCode;
use App\Support\DestroyObject;
use Log;
use Maatwebsite\Excel\Facades\Excel;
use Prettus\Repository\Exceptions\RepositoryException;
use Prettus\Validator\Exceptions\ValidatorException;
use App\Constants\Order as OrderConstant;
use Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
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

    protected $masterRepository;

    protected $stockRepository;

    /**
     * CustomerController constructor.
     * @param CustomerRepository      $customerRepository
     * @param PurchaseOrderRepository $purchaseOrderRepository
     * @param SalesOrderRepository    $salesOrderRepository
     * @param YarnOrderRepository     $yarnOrderRepository
     * @param WastageOrderRepository  $wastageOrderRepository
     * @param AgentRepository         $agentRepository
     * @param MasterRepository        $masterRepository
     * @param StockRepository         $stockRepository
     */
    public function __construct(
        CustomerRepository $customerRepository,
        PurchaseOrderRepository $purchaseOrderRepository,
        SalesOrderRepository $salesOrderRepository,
        YarnOrderRepository $yarnOrderRepository,
        WastageOrderRepository $wastageOrderRepository,
        AgentRepository $agentRepository,
        MasterRepository $masterRepository,
        StockRepository $stockRepository
    ) {
        $this->customerRepository = $customerRepository;
        $this->purchaseOrderRepository = $purchaseOrderRepository;
        $this->salesOrderRepository = $salesOrderRepository;
        $this->yarnOrderRepository = $yarnOrderRepository;
        $this->wastageOrderRepository = $wastageOrderRepository;
        $this->agentRepository = $agentRepository;
        $this->masterRepository = $masterRepository;
        $this->stockRepository = $stockRepository;
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
                HTTPCode::UNPROCESSABLE_ENTITY, $exception);
        }
    }

    /**
     * @param      $customer
     * @param      $input
     * @param bool $export
     * @return array
     * @throws Exception
     */
    private function orderList($customer, $input, $export = false) {
        switch ($input['order_type']) {
            case OrderConstant::PURCHASE_ORDER:
                return $this->purchaseOrderRepository->customerOrders($customer->id, $input,
                    $export);

            case OrderConstant::FABRIC_ORDER:
                $statuses = $this->fabricMeterStatuses();

                return $this->salesOrderRepository->customerOrders($statuses[Master::SO_DELIVERED]['id'],
                    [
                        $statuses[Master::SO_MANUFACTURING]['id'],
                        $statuses[Master::SO_COMPLETED]['id']
                    ], $customer->id, $input, $export);

            case OrderConstant::YARN_ORDER:
                return $this->yarnOrderRepository->customerOrders($customer->id, $input, $export);

            default:
                return $this->wastageOrderRepository->customerOrders($customer->id, $input,
                    $export);

        }
    }

    /**
     * @return mixed
     */
    private function fabricMeterStatuses() {
        return $this->masterRepository->findWhereIn('code',
            [Master::SO_DELIVERED, Master::SO_MANUFACTURING, Master::SO_COMPLETED], ['id', 'code'])
                                      ->keyBy('code')
                                      ->all();
    }

    /**
     * @param Customer      $customer
     * @param LedgerRequest $request
     * @return JsonResponse|BinaryFileResponse
     */
    public function exportLedger(Customer $customer, LedgerRequest $request) {
        $input = $request->all();
        try {
            $orders = $this->orderList($customer, $input, true);
            if (($orders = collect($orders->getData()->data))->isEmpty()) {
                return $this->sendResponse(null,
                    __('messages.can_not_export', ['module' => 'Orders']),
                    HTTPCode::OK);
            }

            return Excel::download(new ExportLedger($orders, $customer, $input['order_type']),
                'ledger.xlsx');
        } catch (Exception $exception) {
            Log::error($exception);

            return $this->sendResponse(null, __('messages.something_wrong'),
                HTTPCode::UNPROCESSABLE_ENTITY, $exception);
        }
    }


}



