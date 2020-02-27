<?php

namespace Knovators\Authentication\Http\Controllers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Knovators\Authentication\Common\CommonService;
use Knovators\Authentication\Constants\Role as RoleConstant;
use Knovators\Authentication\Models\User;
use Knovators\Authentication\Repository\RoleRepository;
use Knovators\Authentication\Repository\UserRepository;
use Knovators\Support\Helpers\HTTPCode;
use Knovators\Support\Traits\APIResponse;


/**
 * Class RegisterController
 * @package Knovators\Authentication\Http\Controllers
 */
class RegisterController extends Controller
{

    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers, APIResponse;

    protected $roleRepository;

    protected $userRepository;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/home';


    /**
     * Create a new controller instance.
     * @param RoleRepository $roleRepository
     * @param UserRepository $userRepository
     */
    public function __construct(RoleRepository $roleRepository, UserRepository $userRepository) {
        $this->roleRepository = $roleRepository;
        $this->userRepository = $userRepository;
        $this->middleware('guest');
    }


    /**
     * @param Request $request
     * @return JsonResponse|mixed
     * @throws \Exception
     */
    public function register(Request $request) {
        try {
            $validator = $this->validator($request->all());
            if ($validator->fails()) {
                return $this->sendResponse(null, $validator->errors(),
                    HTTPCode::UNPROCESSABLE_ENTITY);
            }
            event(new Registered($user = $this->create($request->all())));

            return $this->registered($request, $user);
        } catch (\Exception $exception) {
            Log::error($exception);

            return $this->sendResponse(null, trans('authentication::messages.something_wrong'),
                HTTPCode::UNPROCESSABLE_ENTITY, $exception);
        }

    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param array $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data) {
        return Validator::make($data, [
            'first_name' => 'required|string|max:60',
            'last_name'  => 'required|string|max:60',
            'email'      => 'required|string|email|max:60|unique:users',
            'phone'      => 'required|numeric|digits:10|unique:users',
            'password'   => 'required|string|min:6',
            'image_id'   => 'nullable|exists:files,id'
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param array $data
     * @return User|JsonResponse
     * @throws \Exception
     */
    protected function create(array $data) {
        try {
            $user = $this->userRepository->create([
                'first_name' => $data['first_name'],
                'last_name'  => $data['last_name'],
                'email'      => $data['email'],
                'phone'      => $data['phone'],
                'password'   => Hash::make($data['password']),
            ]);

            $connection = 'assignRole' . config('authentication.db');
            $role = $this->roleRepository->getRole(RoleConstant::USER);
            $this->$connection($user, $role);
            return $user;

        } catch (Exception $exception) {
            throw $exception;
        }
    }

    /**
     * The user has been registered.
     *
     * @param Request $request
     * @param mixed   $user
     * @return mixed
     * @throws \Exception
     */
    protected function registered(Request $request, $user) {
        try {
            $key = mt_rand(100000, 999999);
            $hashKey = Hash::make($user->email . $key);
            $user->update([
                'email_verification_key' => $key,
            ]);
            $user->sendVerificationMail($hashKey);
            $user->new_token = null;
            return $this->sendResponse($this->makeResource($user),
                trans('authentication::messages.user_registered'),
                HTTPCode::CREATED);
        } catch (Exception $exception) {
            throw $exception;
        }
    }

    /**
     * @param User $user
     * @return mixed
     */
    private function makeResource($user) {
        $resource = CommonService::getClass('user_resource');

        return new $resource($user);
    }

    /**
     * @param $user
     * @param $role
     */
    private function assignRoleMysql($user, $role) {
        try {
            $user->roles()->sync([$role->id]);
        } catch (Exception $exception) {
            throw $exception;
        }
    }

    /**
     * @param $user
     * @param $role
     */
    private function assignRoleMongodb($user, $role) {
        try {
            $user->update(['roles' => [$role->id]]);
        } catch (Exception $exception) {
            throw $exception;
        }
    }

}
