<?php

namespace App\Http\Controllers;

use App\Models\Master;
use Exception;
use Illuminate\Http\JsonResponse;
use Knovators\Masters\Http\Requests\Masters\SubMasterRequest;
use Knovators\Masters\MasterService;
use App\Repositories\MasterRepository;
use Knovators\Support\Helpers\HTTPCode;
use Log;
use App\Constants\Master as MasterConstant;

/**
 * Class MasterController
 * @package App\Http\Controllers
 */
class MasterController extends Controller
{

    private $masterRepo;

    use MasterService;


    /**
     * MasterController constructor.
     * @param MasterRepository $masterRepo
     */
    public function __construct(MasterRepository $masterRepo) {
        $this->masterRepo = $masterRepo;
    }

    /**
     * @param SubMasterRequest $request
     * @return JsonResponse
     */
    public function childMasters(SubMasterRequest $request) {
        $input = $request->all();
        try {
            $colorMaster = $this->masterRepo->findByCode(MasterConstant::COLOR);
            $masters = $this->masterRepo->subMasterList($input['parent_id'], $colorMaster);

            /** @var Master $masters * */
            return $this->sendResponse($this->makeResourceCollection($masters),
                trans('masters::messages.retrieved', ['module' => 'Sub masters']),
                HTTPCode::OK);
        } catch (Exception $exception) {
            Log::error($exception);

            return $this->sendResponse(null, __('masters::messages.something_wrong'),
                HTTPCode::UNPROCESSABLE_ENTITY);
        }
    }


}



