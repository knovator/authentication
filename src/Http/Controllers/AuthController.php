<?php

namespace Knovators\Authentication\Http\Controllers;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Knovators\Authentication\Http\Requests\ForgotPasswordRequest;
use Knovators\Authentication\Http\Requests\ResetPasswordRequest;
use Knovators\Authentication\Http\Requests\VerificationFormRequest;
use Knovators\Authentication\Models\User;
use Knovators\Authentication\Repository\UserRepository;
use Knovators\Support\Helpers\HTTPCode;
use Knovators\Support\Traits\APIResponse;
use Laravel\Passport\Token;
use Prettus\Repository\Exceptions\RepositoryException;

/**
 * Class AuthController
 * @package Knovators\Authentication\Http\Controllers
 */
class AuthController extends Controller
{

    use APIResponse;

    private $userRepository;

    /**
     * Create a new controller instance.
     *
     * @param UserRepository $userRepository
     */
    public function __construct(UserRepository $userRepository) {
        $this->userRepository = $userRepository;
        $this->middleware('guest');
    }


    /**
     * @param ForgotPasswordRequest $request
     * @return JsonResponse
     * @throws RepositoryException
     */
    public function forgotPassword(ForgotPasswordRequest $request) {
        $input = $request->all();
        $user = $this->getUser($input);
        if (!$user) {
            return $this->sendResponse(null,
                trans('authentication::messages.user_not_registered'),
                HTTPCode::UNPROCESSABLE_ENTITY);
        }
        try {
            $key = mt_rand(100000, 999999);
            $hashKey = $this->hashMake($user->email . $key);
            $this->updatePrimaryAccount($user, [
                'email_verification_key' => $key
            ]);
            $user->sendPasswordResetNotification($hashKey);

            return $this->sendResponse(null,
                trans('authentication::messages.forget_password'),
                HTTPCode::OK);

        } catch (Exception $exception) {
            Log::error($exception);

        }

        return $this->sendResponse(null, trans('authentication::messages.something_wrong'),
            HTTPCode::UNPROCESSABLE_ENTITY, $exception);

    }


    /**
     * @param $input
     * @return mixed
     * @throws RepositoryException
     */
    private function getUser($input) {
        return $this->userRepository->with('primaryAccount')->findBy('email', $input['email']);
    }

    /**
     * @param $parameters
     * @return mixed
     */
    public function hashMake($parameters) {
        return Hash::make($parameters);
    }

    /**
     * @param $user
     * @param $values
     */
    private function updatePrimaryAccount($user, $values) {
        /** @var User $user */
        $user->primaryAccount()->update($values);
    }

    /**
     * @param ResetPasswordRequest $request
     * @return JsonResponse
     * @throws RepositoryException
     */
    public function resetPassword(ResetPasswordRequest $request) {
        $input = $request->all();
        $user = $this->getUser($input);
        if (!$user) {
            return $this->sendResponse(null,
                trans('authentication::messages.user_not_registered', ['module' => 'User']),
                HTTPCode::UNPROCESSABLE_ENTITY);
        }
        try {
            if ($this->hasCheck($user->email . $user->primaryAccount->email_verification_key,
                $input['token'])) {
                $this->revokeTokens($user);
                $user->update([
                    'password' => $this->hashMake($input['password']),
                ]);
                $this->updatePrimaryAccount($user, [
                    'email_verification_key' => null
                ]);

                return $this->sendResponse($user->fresh(),
                    trans('authentication::messages.password_reset'),
                    HTTPCode::OK);
            }

            return $this->sendResponse(null, trans('authentication::messages.something_wrong'),
                HTTPCode::BAD_REQUEST);

        } catch (Exception $exception) {
            Log::error($exception);
        }

        return $this->sendResponse(null, trans('authentication::messages.something_wrong'),
            HTTPCode::UNPROCESSABLE_ENTITY, $exception);
    }

    /**
     * @param $source
     * @param $target
     * @return bool
     */
    public function hasCheck($source, $target) {
        return Hash::check($source, $target);
    }

    /**
     * @param null $user
     * @return bool
     * @internal param $input
     */
    public function revokeTokens($user) {
        /** @var User $user */
        if ($user->tokens->isNotEmpty()) {
            foreach ($user->tokens as $token) {
                /** @var Token $token */
                $token->revoke();
            }
        }

        return true;
    }

    /**
     * @param VerificationFormRequest $request
     * @return mixed
     */
    public function verify(VerificationFormRequest $request) {
        $input = $request->all();
        $user = $this->getUser($input);
        if (!$user) {
            return $this->sendResponse(null, __('messages.not_found', ['module' => 'User']),
                HTTPCode::UNPROCESSABLE_ENTITY);
        }
        if (!$user->isVerified()) {
            if (Hash::check($user->email . $user->primaryAccount->email_verification_key,
                $input['key'])) {
                return $this->createEmailVerification($user);
            }

            return $this->sendResponse(null, __('messages.invalid_url'),
                HTTPCode::UNPROCESSABLE_ENTITY);
        }

        return $this->sendResponse(null,
            __('messages.already_verified', ['type' => ucwords($input['type'])]),
            HTTPCode::OK);
    }

    /**
     * @param $user
     * @return mixed
     */
    private function createEmailVerification($user) {
        try {
            $this->updatePrimaryAccount($user, [
                'is_verified'            => 1,
                'email_verification_key' => null
            ]);

            return redirect(config('authentication.front_url') . 'login/?verified=true');
        } catch (Exception $exception) {
            Log::error($exception);

            return $this->sendResponse(null,
                __('messages.something_wrong'),
                HTTPCode::OK);
        }
    }

}
