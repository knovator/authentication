<?php

namespace App\Modules\Design\Http\Controllers;

use App\Constants\GenerateNumber;
use App\Http\Controllers\Controller;
use App\Modules\Design\Http\Requests\CreateRequest;
use App\Modules\Design\Models\Design;
use App\Modules\Design\Models\DesignBeam;
use App\Modules\Design\Repositories\DesignRepository;
use App\Support\UniqueIdGenerator;
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

    use DestroyObject, UniqueIdGenerator;

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
        try {
            DB::beginTransaction();
            $input['design_no'] = $this->generateUniqueId(GenerateNumber::DESIGN);
            $design = $this->designRepository->create($input);
            $this->storeDesignDetails($design, $input);
            DB::commit();

            return $this->sendResponse($design,
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
    private function storeDesignDetails(Design $design, $input) {
        $design->detail()->create($input);
        $design->images()->createMany($input['images']);
        $design->fiddlePicks()->createMany($input['fiddle_picks']);
        $this->storeDesignBeams($design, $input);
    }


    /**
     * @param Design $design
     * @param        $input
     */
    private function storeDesignBeams(Design $design, $input) {
        foreach ($input['design_beams'] as $data) {
            $designBeam = $design->beams()->create(['thread_color_id' => $data['beam_id']]);
            /** @var DesignBeam $designBeam */
            $designBeam->recipes()->sync($data['recipes_id']);
        }
    }
}



