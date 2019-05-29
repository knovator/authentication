<?php

namespace App\Modules\Design\Http\Controllers;

use App\Constants\GenerateNumber;
use App\Http\Controllers\Controller;
use App\Http\Requests\PartiallyUpdateRequest;
use App\Modules\Design\Http\Requests\ApproveRequest;
use App\Modules\Design\Http\Requests\CreateRequest;
use App\Modules\Design\Http\Requests\UpdateRequest;
use App\Modules\Design\Models\Design;
use App\Modules\Design\Models\DesignBeam;
use App\Modules\Design\Repositories\DesignRepository;
use App\Support\UniqueIdGenerator;
use DB;
use Exception;
use Illuminate\Http\JsonResponse;
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
        $design->detail()->updateOrCreate([], $input);
        $this->storeDesignAttributes($design, $input, 'images', 'images');
        $this->storeDesignAttributes($design, $input, 'fiddle_picks', 'fiddlePicks');
        $this->storeDesignBeams($design, $input);
    }


    /**
     * @param Design        $design
     * @param UpdateRequest $request
     * @return JsonResponse
     */
    public function update(Design $design, UpdateRequest $request) {
        $input = $request->all();
        if ($design->is_approved) {
            return $this->sendResponse(null, __('messages.can_not_edit_design'),
                HTTPCode::UNPROCESSABLE_ENTITY);
        }
        try {
            DB::beginTransaction();
            $design->update($input);
            $this->storeDesignDetails($design, $input);
            DB::commit();

            return $this->sendResponse($design->fresh(),
                __('messages.updated', ['module' => 'Design']),
                HTTPCode::OK);
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error($exception);

            return $this->sendResponse(null, __('messages.something_wrong'),
                HTTPCode::UNPROCESSABLE_ENTITY, $exception);
        }
    }


    /**
     * @param Design $design
     * @param        $input
     * @param        $column
     * @param        $relation
     */
    private function storeDesignAttributes(Design $design, $input, $column, $relation) {
        $newItems = [];
        foreach ($input[$column] as $item) {
            if (isset($item['id'])) {
                $design->$relation()->whereId($item['id'])->update($item);
            } else {
                $newItems[] = $item;
            }
        }
        if (!empty($newItems)) {
            $design->$relation()->createMany($newItems);
        }
        $removableLabel = 'removed_' . $column . '_id';

        if (isset($input[$removableLabel]) && !empty($input[$removableLabel])) {
            $design->$relation()->whereIn('id', $input[$removableLabel])->delete();
        }
    }

    /**
     * @param Design $design
     * @param        $input
     */
    private function storeDesignBeams(Design $design, $input) {
        foreach ($input['design_beams'] as $beam) {
            $beamId = null;
            if (isset($beam['id'])) {
                $beamId = $beam['id'];
            }
            $designBeam = $design->beams()
                                 ->updateOrCreate(['id' => $beamId], [
                                     'thread_color_id' =>
                                         $beam['beam_id']
                                 ]);
            /** @var DesignBeam $designBeam */
            $designBeam->recipes()->sync($beam['recipes_id']);
        }
        if (isset($input['removed_design_beams_id']) && !empty($input['removed_design_beams_id'])) {
            $design->beams()->whereIn('id', $input['removed_design_beams_id'])->delete();
        }

    }


    /**
     * @param Design                 $design
     * @param PartiallyUpdateRequest $request
     * @return JsonResponse
     */

    public function partiallyUpdate(Design $design, PartiallyUpdateRequest $request) {
        $design->update($request->all());
        $design->fresh();

        return $this->sendResponse($design,
            __('messages.updated', ['module' => 'Design']),
            HTTPCode::OK);
    }

    /**
     * @param Design         $design
     * @param ApproveRequest $request
     * @return JsonResponse
     */

    public function partiallyApprove(Design $design, ApproveRequest $request) {
        $input = $request->all();
        if ($input['is_approved'] && (!$design->is_active)) {
            return $this->sendResponse(null,
                __('messages.in_active_design', ['module' => 'Design']),
                HTTPCode::UNPROCESSABLE_ENTITY);
        }
        $design->update($input);
        $design->fresh();

        return $this->sendResponse($design,
            __('messages.updated', ['module' => 'Design']),
            HTTPCode::OK);
    }
}



