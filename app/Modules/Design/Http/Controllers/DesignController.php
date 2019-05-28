<?php

namespace App\Modules\Design\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Design\Http\Requests\CreateRequest;
use App\Modules\Design\Repositories\DesignRepository;
use DB;
use Exception;
use Knovators\Support\Helpers\HTTPCode;
use Knovators\Support\Traits\DestroyObject;
use Log;

/**
 * Class DesignController
 * @package App\Modules\Design\Http\Controllers
 */
class DesignController extends Controller
{

    use DestroyObject;

    protected $designRepository;

    /**
     * DesignController constructor.
     * @param DesignRepository $designRepository
     */
    public function __construct(
        DesignRepository $designRepository
    ) {
        $this->designRepository = $designRepository;
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
            $design = $this->designRepository->create($input);
            $this->storeDesignDetails($design, $input);
            DB::commit();

            return $this->sendResponse(null,
                __('messages.created', ['module' => 'Design']),
                HTTPCode::CREATED);
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error($exception);

            return $this->sendResponse(null, __('messages.something_wrong'),
                HTTPCode::UNPROCESSABLE_ENTITY, $exception);
        }
    }

    /**
     * @param $design
     * @param $input
     */
    private function storeDesignDetails($design, $input) {


    }
}



