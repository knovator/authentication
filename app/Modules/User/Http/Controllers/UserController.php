<?php

namespace App\Modules\User\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\User\Http\Requests\ChangePasswordRequest;
use App\Modules\User\Http\Requests\CreateRequest;
use App\Modules\User\Http\Requests\UpdateRequest;
use App\Modules\User\Http\Resources\User as UserResource;
use App\Modules\User\Repositories\UserRepository;
use App\User;
use Auth;
use DB;
use Exception;
use Hash;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\PartiallyUpdateRequest;
use Knovators\Support\Helpers\HTTPCode;
use Knovators\Support\Traits\DestroyObject;
use Log;

/**
 * Class UserController
 * @package App\Modules\User\Http\Controllers
 */
class UserController extends Controller
{

    use DestroyObject;

    private $userRepository;

    /**
     * UserController constructor.
     * @param UserRepository $userRepository
     */
    public function __construct(
        UserRepository $userRepository
    ) {
        $this->userRepository = $userRepository;
    }

    /**
     * @param $password
     * @return string
     */
    private function createHash($password) {
        return Hash::make($password);
    }


    /**
     * @param CreateRequest $request
     * @return mixed
     */
    public function store(CreateRequest $request) {
        $input = $request->all();
        try {
            DB::beginTransaction();
            $input['password'] = $this->createHash($input['password']);
            $user = $this->userRepository->createOrUpdateTrashed('email', $input['email'],
                $input);
            /** @var User $user */
            $user->roles()->attach($input['role_ids']);
            $user->load(['image', 'roles']);
            DB::commit();

            return $this->sendResponse($this->makeResource($user),
                __('messages.created', ['module' => 'User']),
                HTTPCode::CREATED);
        } catch (Exception $exception) {
            DB::rollBack();
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
            $users = $this->userRepository->getUserList();

            return $this->sendResponse($users,
                __('messages.retrieved', ['module' => 'Employees']),
                HTTPCode::OK);
        } catch (Exception $exception) {
            Log::error($exception);

            return $this->sendResponse(null, __('messages.something_wrong'),
                HTTPCode::UNPROCESSABLE_ENTITY);
        }
    }


    /**
     * @param User $user
     * @return UserResource
     */
    private function makeResource($user) {
        return new UserResource($user);
    }

    /**
     * @param User          $user
     * @param UpdateRequest $request
     * @return JsonResponse
     */
    public function update(User $user, UpdateRequest $request) {
        $input = $request->all();
        try {
            if (isset($input['password'])) {
                $input['password'] = $this->createHash($input['password']);
            }
            DB::beginTransaction();
            $user->update($input);
            $user->roles()->sync($input['role_ids']);
            $user->fresh();
            DB::commit();

            return $this->sendResponse($this->makeResource($user->load(['image', 'roles'])),
                __('messages.updated', ['module' => 'User']),
                HTTPCode::OK);
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error($exception);

            return $this->sendResponse(null, __('messages.something_wrong'),
                HTTPCode::UNPROCESSABLE_ENTITY, $exception);
        }

    }


    /**
     * @param User $user
     * @return JsonResponse
     */
    public function destroy(User $user) {
        try {
            // user relations
            $relations = [

            ];

            return $this->destroyModelObject($relations, $user, 'User');

        } catch (Exception $exception) {
            Log::error($exception);

            return $this->sendResponse(null, __('messages.something_wrong'),
                HTTPCode::UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * @param User                   $user
     * @param PartiallyUpdateRequest $request
     * @return JsonResponse
     */

    public function partiallyUpdate(User $user, PartiallyUpdateRequest $request) {
        $input = $request->all();
        $user->update($input);
        $user->fresh();

        return $this->sendResponse($this->makeResource($user->load(['image', 'roles'])),
            __('messages.updated', ['module' => 'User']),
            HTTPCode::OK);
    }


    /**
     * @param User $user
     * @return JsonResponse
     */
    public function show(User $user) {
        $user->load(['image', 'roles']);

        return $this->sendResponse($this->makeResource($user),
            __('messages.retrieved', ['module' => 'User']),
            HTTPCode::OK);
    }


    /**
     * @param ChangePasswordRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function changePassword(ChangePasswordRequest $request) {
        $input = $request->all();
        try {
            /** @var User $user */
            $user = Auth::user();
            if (!Hash::check($input['current_password'], $user->password)) {
                return $this->sendResponse(null,
                    __('messages.current_password_wrong'),
                    HTTPCode::UNPROCESSABLE_ENTITY);
            }
            $user->update(['password' => $this->createHash($input['password'])]);

            return $this->sendResponse(null,
                __('messages.password_changed'),
                HTTPCode::OK);
        } catch (\Exception $exception) {
            Log::error($exception);

            return $this->sendResponse(null, __('messages.something_wrong'),
                HTTPCode::UNPROCESSABLE_ENTITY, $exception);
        }

    }


}
