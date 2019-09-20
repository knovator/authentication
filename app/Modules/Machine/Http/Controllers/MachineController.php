<?php

namespace App\Modules\Machine\Http\Controllers;

use App\Constants\Master;
use App\Http\Controllers\Controller;
use App\Http\Requests\PartiallyUpdateRequest;
use App\Modules\Machine\Http\Requests\CreateRequest;
use App\Modules\Machine\Http\Requests\UpdateRequest;
use App\Modules\Machine\Http\Resources\Machine as MachineResource;
use App\Modules\Machine\Models\Machine;
use App\Modules\Machine\Repositories\MachineRepository;
use App\Modules\Sales\Repositories\SalesOrderRepository;
use App\Repositories\MasterRepository;
use DB;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Knovators\Support\Helpers\HTTPCode;
use App\Support\DestroyObject;
use Log;

/**
 * Class MachineController
 * @package App\Modules\Machine\Http\Controllers
 */
class MachineController extends Controller
{

    use DestroyObject;

    protected $machineRepository;

    protected $salesOrderRepository;

    protected $masterRepository;

    /**
     * MachineController constructor.
     * @param MachineRepository    $machineRepository
     * @param SalesOrderRepository $salesOrderRepository
     * @param MasterRepository     $masterRepository
     */
    public function __construct(
        MachineRepository $machineRepository,
        SalesOrderRepository $salesOrderRepository,
        MasterRepository $masterRepository
    ) {
        $this->machineRepository = $machineRepository;
        $this->salesOrderRepository = $salesOrderRepository;
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
            $machine = $this->machineRepository->create($input);
            $machine->load([
                'threadColor.thread',
                'threadColor.color'
            ]);

            return $this->sendResponse($this->makeResource($machine),
                __('messages.created', ['module' => 'Machine']),
                HTTPCode::CREATED);
        } catch (Exception $exception) {
            Log::error($exception);

            return $this->sendResponse(null, __('messages.something_wrong'),
                HTTPCode::UNPROCESSABLE_ENTITY, $exception);
        }
    }


    /**
     * @param Machine $machine
     * @return MachineResource
     */
    private function makeResource($machine) {
        return new MachineResource($machine);
    }


    /**
     * @param Machine $machine
     * @return JsonResponse
     */
    public function show(Machine $machine) {

        $machine->load([
            'threadColor.thread',
            'threadColor.color'
        ]);


        return $this->sendResponse($this->makeResource($machine),
            __('messages.retrieved', ['module' => 'Machine']),
            HTTPCode::OK);
    }

    /**
     * @param Machine       $machine
     * @param UpdateRequest $request
     * @return JsonResponse
     * @throws Exception
     */
    public function update(Machine $machine, UpdateRequest $request) {
        $input = $request->all();
        try {
            $machine->update($input);
            $machine->fresh();

            return $this->sendResponse($this->makeResource($machine->load([
                'threadColor.thread',
                'threadColor.color'
            ])),
                __('messages.updated', ['module' => 'Machine']),
                HTTPCode::OK);
        } catch (Exception $exception) {
            Log::error($exception);

            return $this->sendResponse(null, __('messages.something_wrong'),
                HTTPCode::UNPROCESSABLE_ENTITY, $exception);
        }
    }

    /**
     * @param Machine                $machine
     * @param PartiallyUpdateRequest $request
     * @return JsonResponse
     */

    public function partiallyUpdate(Machine $machine, PartiallyUpdateRequest $request) {
        $machine->update($request->all());
        $machine->fresh();

        return $this->sendResponse($this->makeResource($machine->load([
            'threadColor.thread',
            'threadColor.color'
        ])),
            __('messages.updated', ['module' => 'Machine']),
            HTTPCode::OK);
    }


    /**
     * @param Machine $machine
     * @return JsonResponse
     */
    public function destroy(Machine $machine) {
        try {
            // Machine associated relations
            $relations = [
                'soPartialOrders'
            ];

            return $this->destroyModelObject($relations, $machine, 'Machine');

        } catch (Exception $exception) {
            Log::error($exception);

            return $this->sendResponse(null, __('messages.something_wrong'),
                HTTPCode::UNPROCESSABLE_ENTITY, $exception);
        }
    }

    /**
     * @return JsonResponse
     */
    public function index() {
        try {
            $statusId = $this->masterRepository->findByCode(Master::SO_PENDING)->id;
            $machines = $this->machineRepository->getMachineList($statusId);

            return $this->sendResponse($machines,
                __('messages.retrieved', ['module' => 'Machines']),
                HTTPCode::OK);
        } catch (Exception $exception) {
            Log::error($exception);

            return $this->sendResponse(null, __('messages.something_wrong'),
                HTTPCode::UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function activeMachines(Request $request) {
        $input = $request->all();
        try {
            if (isset($input['sales_order_id'])) {
                $input['sales_order'] = $this->salesOrderRepository->with([
                    'design.detail',
                    'designBeam'
                ])
                                                                   ->find($input['sales_order_id']);
            }
            $machines = $this->machineRepository->getActiveMachines($input);

            return $this->sendResponse($machines,
                __('messages.retrieved', ['module' => 'Machines']),
                HTTPCode::OK);
        } catch (Exception $exception) {
            Log::error($exception);

            return $this->sendResponse(null, __('messages.something_wrong'),
                HTTPCode::UNPROCESSABLE_ENTITY);
        }
    }

}



