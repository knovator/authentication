<?php

namespace Knovators\Authentication\Http\Controllers;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Knovators\Authentication\Common\CommonService;
use Knovators\Authentication\Constants\UserConstant as UserConstant;
use Knovators\Authentication\Http\Requests\ForgotPasswordRequest;
use Knovators\Authentication\Http\Requests\ResetPasswordRequest;
use Knovators\Authentication\Http\Requests\VerificationFormRequest;
use Knovators\Authentication\Models\User;
use Knovators\Authentication\Models\UserAccount;
use Knovators\Authentication\Repository\AccountRepository;
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
    private $accountRepository;

    /**
     * Create a new controller instance.
     *
     * @param UserRepository    $userRepository
     * @param AccountRepository $accountRepository
     */
    public function __construct(
        UserRepository $userRepository,
        AccountRepository $accountRepository
    ) {
        $this->userRepository = $userRepository;
        $this->accountRepository = $accountRepository;
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
            if (isset($input['email'])) {
                $message = '';
                $key = mt_rand(100000, 999999);
                $hashKey = $this->hashMake($user->email . $key);
                $this->updatePrimaryAccount($user, [
                    'email_verification_key' => $key
                ]);
                $user->sendPasswordResetNotification($hashKey);
            } else {
                $message = '_phone';
                CommonService::sendMessage($input);
            }

            return $this->sendResponse(null,
                trans('authentication::messages.forget_password' . $message),
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
        $user = $this->userRepository->with('primaryAccount');
        if (isset($input['email'])) {
            return $user->findBy('email', $input['email']);
        }

        return $user->findBy('phone', $input['phone']);

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
        $type = $input['type'];
        $user = $this->getUser($input);
        if (!$user) {
            return $this->sendResponse(null,
                trans('authentication::messages.user_not_registered', ['module' => 'User']),
                HTTPCode::UNPROCESSABLE_ENTITY);
        }
        try {
            $response = $this->sendResponse($user->fresh(),
                trans('authentication::messages.password_reset'),
                HTTPCode::OK);
            if ($type == UserConstant::TYPE_EMAIL) {
                if ($this->hasCheck($user->email . $user->primaryAccount->email_verification_key,
                    $input['token'])) {
                    $this->revokeTokens($user);
                    $user->update([
                        'password' => $this->hashMake($input['password']),
                    ]);
                    $this->updatePrimaryAccount($user, [
                        'email_verification_key' => null
                    ]);

                    return $response;

                }
            } else {
                if (CommonService::verifyOtp($input)) {
                    $this->updatePrimaryAccount($user, [
                        'password' => $this->hashMake($input['password'])
                    ]);

                    return $response;
                }
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
     * @return JsonResponse|mixed
     */
    public function verify(VerificationFormRequest $request) {
        $input = $request->all();
        $type = $input['type'];
        try {
            /** @var UserAccount $userAccount */
            $userAccount = $this->getUserAccount($input);
            if (!$userAccount) {
                return $this->sendResponse(null, __('messages.not_found', ['module' => 'User']),
                    HTTPCode::UNPROCESSABLE_ENTITY);
            }
            if (!$userAccount->isVerified()) {
                if ($type == UserConstant::TYPE_EMAIL) {
                    $message = 'url';
                    if (Hash::check($userAccount->email .
                        $userAccount->email_verification_key,
                        $input['key'])) {

                        return $this->createEmailVerification($userAccount);
                    }
                } else {
                    $message = 'otp';
                    if (CommonService::verifyOtp($input)) {
                        $userAccount->update(['is_verified' => 1]);

                        return $this->redirectUrl();
                    }
                }

                return $this->sendResponse(null, __('messages.invalid_' . $message),
                    HTTPCode::BAD_REQUEST);
            }

            return $this->sendResponse(null,
                __('messages.already_verified', ['type' => ucwords($input['type'])]),
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
    private function getUserAccount($input) {

        $userAccount = $this->accountRepository;
        if (isset($input['email'])) {
            return $userAccount->findBy('email', $input['email']);
        }

        return $userAccount->findBy('phone', $input['phone']);
    }

    /**
     * @param $userAccount
     * @return mixed
     */
    private function createEmailVerification($userAccount) {
        $userAccount->update([
            'is_verified'            => 1,
            'email_verification_key' => null
        ]);

        return $this->redirectUrl();

    }

    /**
     * @return RedirectResponse|Redirector
     */
    private function redirectUrl() {
        return redirect(config('authentication.front_url') . 'login/?verified=true');

    }

}
