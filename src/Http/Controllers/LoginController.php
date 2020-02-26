<?php

namespace Knovators\Authentication\Http\Controllers;

use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Knovators\Authentication\Common\CommonService;
use Knovators\Authentication\Models\User;
use Knovators\Support\Helpers\HTTPCode;
use Knovators\Support\Traits\APIResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class LoginController
 * @package Knovators\Authentication\Http\Admin\Controllers
 */

class LoginController extends Controller
{

    use APIResponse, AuthenticatesUsers;


    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';


    /**
     * Create a new controller instance.
     */
    public function __construct() {
//        $this->middleware('guest')->except('logout');
    }


    /**
     * @param Request $request
     * @return array
     */
    protected function credentials(Request $request) {

        if (!$columns = config('authentication.login_columns')) {
            $columns = 'email,phone';
        }

        return [
            $columns   => $request->get($this->username()),
            'password' => $request->get('password')
        ];
    }

    /**
     * @param Request $request
     * @return $this|ResponseFactory|RedirectResponse|Response
     */
    protected function sendLoginResponse(Request $request) {
        $this->clearLoginAttempts($request);
        $user = $this->guard()->user();
        /** @var User $user */
        if (!$user->isActive() || !$user->emailVerified()) {
            $message = trans('authentication::messages.confirm_email');
            if (!$user->isActive()) {
                $message = trans('authentication::messages.account_deactivated');
            }
            auth()->logout();

            return $this->sendResponse(null, $message,
                HTTPCode::UNAUTHORIZED);
        }

        $user->new_token = $user->createToken('Client Token')->accessToken;
        if (!$user->new_token) {
            auth()->logout();

            return $this->sendResponse(null, trans('authentication::messages.something_wrong'),
                HTTPCode::UNAUTHORIZED);
        }

        $role = $user->orderByRoles()->with('permissions')->first();

        $user->role = $role->name;

        $user->permissions = collect($role->permissions->groupBy('module'))->map(function ($item) {
            return array_column($item->toArray(), 'route_name');
        });
        $user->load('image');
        return $this->sendResponse($this->makeResource($user),
            trans('authentication::messages.user_login_success'),
            HTTPCode::OK);

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
     * @param Request $request
     * @return $this|ResponseFactory|RedirectResponse|Response
     */
    protected function sendFailedLoginResponse(Request $request) {
        return $this->sendResponse(null, trans('authentication::messages.user_not_registered'),
            HTTPCode::UNPROCESSABLE_ENTITY);
    }


    /**
     * @return JsonResponse
     */
    public function logout() {
        try {
            $user = auth()->guard('api')->user();
            if (!$user) {
                return $this->sendResponse(null, trans('authentication::messages.token_invalid'),
                    HTTPCode::BAD_REQUEST);
            }
            $user->token()->revoke();

            return $this->sendResponse(null, trans('authentication::messages.user_logout_access'),
                HTTPCode::OK);
        } catch (\Exception $exception) {
            Log::error($exception);

            return $this->sendResponse(null, trans('authentication::messages.something_wrong'),
                HTTPCode::UNPROCESSABLE_ENTITY, $exception);
        }
    }


}
