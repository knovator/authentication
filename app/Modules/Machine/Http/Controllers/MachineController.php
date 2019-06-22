<?php

namespace App\Modules\Machine\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\PartiallyUpdateRequest;
use App\Modules\Machine\Http\Requests\CreateRequest;
use App\Modules\Machine\Http\Requests\UpdateRequest;
use App\Modules\Machine\Http\Resources\Machine as MachineResource;
use App\Modules\Machine\Models\Machine;
use App\Modules\Machine\Repositories\MachineRepository;
use Exception;
use Illuminate\Http\JsonResponse;
use Knovators\Support\Helpers\HTTPCode;
use Knovators\Support\Traits\DestroyObject;
use Log;

/**
 * Class MachineController
 * @package App\Modules\Machine\Http\Controllers
 */
class MachineController extends Controller
{

    use DestroyObject;

    protected $machineRepository;

    /**
     * MachineController constructor.
     * @param MachineRepository $machineRepository
     */
    public function __construct(
        MachineRepository $machineRepository
    ) {
        $this->machineRepository = $machineRepository;
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
            $machines = $this->machineRepository->getMachineList();

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
     * @return JsonResponse
     */
    public function activeMachines() {
        try {
            $machines = $this->machineRepository->getActiveMachines();

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



