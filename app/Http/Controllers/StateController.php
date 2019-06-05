<?php

namespace App\Http\Controllers;

use App\Repositories\StateRepository;
use DougSisk\CountryState\CountryState;
use Exception;
use Illuminate\Http\JsonResponse;
use Knovators\Support\Helpers\HTTPCode;
use Log;

/**
 * Class StateController
 * @package App\Http\Controllers
 */
class StateController extends Controller
{

    protected $stateRepository;

    /**
     * StateController constructor.
     * @param StateRepository $stateRepository
     */
    public function __construct(
        StateRepository $stateRepository
    ) {
        $this->stateRepository = $stateRepository;
    }

    /**
     * @return JsonResponse
     */
    public function activeStates() {
        try {
            $states = $this->stateRepository->activeStateList();

            return $this->sendResponse($states,
                __('messages.retrieved', ['module' => 'States']),
                HTTPCode::OK);


        } catch (Exception $exception) {
            Log::error($exception);

            return $this->sendResponse(null, __('messages.something_wrong'),
                HTTPCode::UNPROCESSABLE_ENTITY);
        }
    }

}



