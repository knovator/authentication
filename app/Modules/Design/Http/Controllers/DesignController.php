<?php

namespace App\Modules\Design\Http\Controllers;

use App\Constants\GenerateNumber;
use App\Http\Controllers\Controller;
use App\Http\Requests\PartiallyUpdateRequest;
use App\Modules\Design\Http\Requests\ApproveRequest;
use App\Modules\Design\Http\Requests\CreateRequest;
use App\Modules\Design\Http\Requests\UpdateRequest;
use App\Modules\Design\Http\Resources\Design as DesignResource;
use App\Modules\Design\Models\Design;
use App\Modules\Design\Models\DesignBeam;
use App\Modules\Design\Repositories\DesignDetailRepository;
use App\Modules\Design\Repositories\DesignRepository;
use App\Support\DestroyObject;
use App\Support\UniqueIdGenerator;
use Barryvdh\Snappy\Facades\SnappyPdf;
use DB;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Knovators\Support\Helpers\HTTPCode;
use Log;
use Prettus\Validator\Exceptions\ValidatorException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class DesignController
 * @package App\Modules\Design\Http\Controllers
 */
class DesignController extends Controller
{

    use DestroyObject, UniqueIdGenerator;

    protected $designRepository;

    protected $designDetailRepository;

    /**
     * DesignController constructor.
     * @param DesignRepository       $designRepository
     * @param DesignDetailRepository $designDetailRepository
     */
    public function __construct(
        DesignRepository $designRepository,
        DesignDetailRepository $designDetailRepository
    ) {
        $this->designRepository = $designRepository;
        $this->designDetailRepository = $designDetailRepository;
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

            return $this->sendResponse($this->makeResource($design->load('detail')),
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
     * @throws ValidatorException
     */
    private function storeDesignDetails(Design $design, $input) {
        $this->designDetailRepository->updateOrCreate(['design_id' => $design->id], $input);
        $this->storeDesignAttributes($design, $input, 'images', 'images');
        $this->storeDesignAttributes($design, $input, 'fiddle_picks', 'fiddlePicks');
        $this->storeDesignBeams($design, $input);
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
            $beamId = isset($beam['id']) ? $beam['id'] : null;
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
     * @param Design $design
     * @return DesignResource
     */
    private function makeResource($design) {
        return new DesignResource($design);
    }

    /**
     * @param Design        $design
     * @param UpdateRequest $request
     * @return JsonResponse
     * @throws Exception
     */
    public function update(Design $design, UpdateRequest $request) {
        $input = $request->all();
        try {
            DB::beginTransaction();
            $design->update($input);
            $this->storeDesignDetails($design, $input);
            DB::commit();

            return $this->sendResponse($this->makeResource($design->fresh('detail')),
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
     * @param Design                 $design
     * @param PartiallyUpdateRequest $request
     * @return JsonResponse
     */

    public function partiallyUpdate(Design $design, PartiallyUpdateRequest $request) {
        $design->update($request->all());

        return $this->sendResponse($this->makeResource($design->fresh('detail')),
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

        return $this->sendResponse($this->makeResource($design->fresh('detail')),
            __('messages.updated', ['module' => 'Design']),
            HTTPCode::OK);
    }

    /**
     * @param Design $design
     * @return JsonResponse
     */
    public function destroy(Design $design) {
        try {
            // Design associated relations
            $relations = [
                'salesOrders','wastageOrders'
            ];

            return $this->destroyModelObject($relations, $design, 'Design');

        } catch (Exception $exception) {
            Log::error($exception);

            return $this->sendResponse(null, __('messages.something_wrong'),
                HTTPCode::UNPROCESSABLE_ENTITY, $exception);
        }
    }

    /**
     * @param Design $design
     * @return JsonResponse
     */
    public function show(Design $design) {
        $design->load([
            'detail',
            'fiddlePicks',
            'beams' => function ($beams) {
                /** @var Builder $beams */
                $beams->withCount('sales as used_count')->with([
                    'threadColor.thread',
                    'threadColor.color',
                    'recipes' => function ($recipes) {
                        /** @var Builder $recipes */
                        $recipes->with([
                            'fiddles.thread',
                            'fiddles.color'
                        ]);
                    }
                ]);
            },
            'images.file',
        ]);


        foreach ($design->beams as $beam) {
            /** @var DesignBeam $beam */
            $beam->recipes->loadCount([
                'salesOrders as used_count' => function ($salesOrderRecipes) use ($beam) {
                    /** @var Builder $salesOrderRecipes */
                    $salesOrderRecipes->whereHas('salesOrder', function ($salesOrder) use ($beam) {
                        /** @var Builder $salesOrder */
                        $salesOrder->where('design_beam_id', '=', $beam->id);

                    });

                }
            ]);

        }

        return $this->sendResponse($this->makeResource($design),
            __('messages.retrieved', ['module' => 'Design']),
            HTTPCode::OK);
    }

    /**
     * @return JsonResponse
     */
    public function index() {
        try {
            $designs = $this->designRepository->getDesignList();

            return $this->sendResponse($designs,
                __('messages.retrieved', ['module' => 'Designs']),
                HTTPCode::OK);
        } catch (Exception $exception) {
            Log::error($exception);

            return $this->sendResponse(null, __('messages.something_wrong'),
                HTTPCode::UNPROCESSABLE_ENTITY, $exception);
        }
    }


    /**
     * @return JsonResponse
     */
    public function activeDesigns() {
        try {
            $designs = $this->designRepository->getActiveDesigns();

            return $this->sendResponse($designs,
                __('messages.retrieved', ['module' => 'Designs']),
                HTTPCode::OK);
        } catch (Exception $exception) {
            Log::error($exception);

            return $this->sendResponse(null, __('messages.something_wrong'),
                HTTPCode::UNPROCESSABLE_ENTITY);
        }
    }


    /**
     * @param Design  $design
     * @param Request $request
     * @return Response
     */
    public function export(Design $design, Request $request) {
        $design->load([
            'detail',
            'fiddlePicks',
            'beams.recipes.fiddles.thread',
            'beams.recipes.fiddles.color',
            'beams.threadColor.thread',
            'beams.threadColor.color:id,name,code',
            'mainImage.file'
        ]);
        $image = SnappyPdf::loadView('receipts.design.design', compact('design'));

        return $image->download($design->design_no . ".pdf");
//        return view('receipts.design.design',compact('design'));

    }
}



