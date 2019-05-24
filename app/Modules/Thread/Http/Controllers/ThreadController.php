<?php

namespace App\Modules\Thread\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Thread\Http\Requests\CreateRequest;
use App\Modules\Thread\Models\Thread;
use App\Modules\Thread\Repositories\ThreadRepository;
use DB;
use Exception;
use Knovators\Support\Helpers\HTTPCode;
use Knovators\Support\Traits\DestroyObject;
use Log;
use App\Modules\Thread\Http\Resources\Thread as ThreadResource;

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

            return $this->sendResponse($this->makeResource($thread->load('type', 'colors')),
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
     * @param Thread $thread
     * @return ThreadResource
     */
    private function makeResource($thread) {
        return new ThreadResource($thread);
    }


}
