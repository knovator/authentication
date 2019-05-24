<?php

namespace App\Modules\Thread\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\PartiallyUpdateRequest;
use App\Modules\Thread\Http\Requests\CreateRequest;
use App\Modules\Thread\Http\Requests\UpdateRequest;
use App\Modules\Thread\Http\Resources\Thread as ThreadResource;
use App\Modules\Thread\Models\Thread;
use App\Modules\Thread\Models\ThreadColor;
use App\Modules\Thread\Repositories\ThreadRepository;
use DB;
use Exception;
use Illuminate\Http\JsonResponse;
use Knovators\Support\Helpers\HTTPCode;
use Knovators\Support\Traits\DestroyObject;
use Log;

/**
 * Class ThreadController
 * @package App\Modules\Thread\Http\Controllers
 */
class ThreadController extends Controller
{

    use DestroyObject;

    protected $threadRepository;

    /**
     * ThreadController constructor.
     * @param ThreadRepository $threadRepository
     */
    public function __construct(
        ThreadRepository $threadRepository
    ) {
        $this->threadRepository = $threadRepository;
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
            $thread = $this->threadRepository->create($input);
            $thread->colors()->attach($input['color_ids']);
            DB::commit();

            return $this->sendResponse($this->makeResource($thread->load('type')),
                __('messages.created', ['module' => 'Thread']),
                HTTPCode::CREATED);
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error($exception);

            return $this->sendResponse(null, __('messages.something_wrong'),
                HTTPCode::UNPROCESSABLE_ENTITY, $exception);
        }
    }


    /**
     * @param Thread        $thread
     * @param UpdateRequest $request
     * @return JsonResponse
     * @throws Exception
     */
    public function update(Thread $thread, UpdateRequest $request) {
        $input = $request->all();
        try {
            DB::beginTransaction();
            $thread->update($input);
            $this->storeThreadColors($thread, $input['color_ids']);
            DB::commit();
            $thread->fresh();

            return $this->sendResponse($this->makeResource($thread->load('type')),
                __('messages.updated', ['module' => 'Thread']),
                HTTPCode::OK);
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error($exception);

            return $this->sendResponse(null, __('messages.something_wrong'),
                HTTPCode::UNPROCESSABLE_ENTITY, $exception);
        }
    }

    /**
     * @param $thread
     * @param $colorIds
     */
    private function storeThreadColors(Thread $thread, $colorIds) {
        $ids = [];
        foreach ($colorIds as $colorId) {
            $threadColor = $thread->threadColors()->firstOrCreate(['color_id' => $colorId]);
            $ids[] = $threadColor->id;
        }
        $thread->threadColors()->whereNotIn('id', $ids)->delete();
    }

    /**
     * @param Thread $thread
     * @return ThreadResource
     */
    private function makeResource($thread) {
        return new ThreadResource($thread);
    }


    /**
     * @param Thread $thread
     * @return JsonResponse
     */
    public function show(Thread $thread) {
        $thread->load(['type', 'threadColors.color']);
        // check associated relations
        $thread->threadColors->map(function ($threadColor) {
            /** @var ThreadColor $threadColor */
            $threadColor->updatable = true;
            if (($threadColor->recipes()->exists()) || ($threadColor->salesOrderQuantities()
                                                                    ->exists())) {
                $threadColor->updatable = false;
            }
        });

        return $this->sendResponse($this->makeResource($thread),
            __('messages.retrieved', ['module' => 'Thread']),
            HTTPCode::OK);
    }


    /**
     * @param Thread                 $thread
     * @param PartiallyUpdateRequest $request
     * @return JsonResponse
     */

    public function partiallyUpdate(Thread $thread, PartiallyUpdateRequest $request) {
        $thread->update($request->all());
        $thread->fresh();

        return $this->sendResponse($this->makeResource($thread->load('type')),
            __('messages.updated', ['module' => 'Thread']),
            HTTPCode::OK);
    }


    /**
     * @return JsonResponse
     */
    public function index() {
        try {
            $users = $this->threadRepository->getThreadList();

            return $this->sendResponse($users,
                __('messages.retrieved', ['module' => 'Threads']),
                HTTPCode::OK);
        } catch (Exception $exception) {
            Log::error($exception);

            return $this->sendResponse(null, __('messages.something_wrong'),
                HTTPCode::UNPROCESSABLE_ENTITY);
        }
    }

}
