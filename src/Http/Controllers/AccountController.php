<?php

namespace Knovators\Authentication\Http\Controllers;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Knovators\Authentication\Common\CommonService;
use Knovators\Authentication\Http\Requests\CreateUserAccountRequest;
use Knovators\Authentication\Http\Requests\PartiallyUpdateRequest;
use Knovators\Authentication\Http\Resources\UserAccount as UserAccountResource;
use Knovators\Authentication\Models\UserAccount;
use Knovators\Authentication\Repository\AccountRepository;
use Knovators\Support\Helpers\HTTPCode;
use Knovators\Support\Traits\APIResponse;
use Knovators\Support\Traits\DestroyObject;


/**
 * Class AccountController
 * @package Knovators\Authentication\Http\Controllers
 */
class AccountController extends Controller
{

    use APIResponse;
    protected $accountRepository;


    /**
     * AccountController constructor.
     * @param AccountRepository $accountRepository
     */
    public function __construct(AccountRepository $accountRepository) {
        $this->accountRepository = $accountRepository;
    }

    /**
     * @return JsonResponse
     */
    public function index() {
        try {
            $userAccountList = $this->getUserAccounts()->get();

            return $this->sendResponse($userAccountList,
                __('authentication::messages.retrieved', ['module' => 'user accounts']),
                HTTPCode::OK);
        } catch (Exception $exception) {
            Log::error($exception);
        }

        return $this->sendResponse(null, __('authentication::messages.something_wrong'),
            HTTPCode::UNPROCESSABLE_ENTITY, $exception);

    }

    /**
     * @return mixed
     */
    public function getUserAccounts() {
        return Auth::user()->with('userAccounts:id,email,phone,is_verified,default');

    }

    /**
     * @param CreateUserAccountRequest $request
     * @return JsonResponse
     */
    public function store(CreateUserAccountRequest $request) {
        $input = $request->all();
        try {
            $user = Auth::user();
            $userAccount = $user->userAccounts()->create($input);
            if (isset($input['email'])) {
                $key = mt_rand(100000, 999999);
                $hashKey = Hash::make($input['email'] . $key);
                /** @var UserAccount $userAccount */
                $userAccount->sendVerificationMail($hashKey);
            } else {
                CommonService::sendMessage($input);
            }

            return $this->sendResponse($this->makeResource($userAccount->fresh()),
                __('authentication::messages.created', ['module' => 'user account']),
                HTTPCode::CREATED);
        } catch (Exception $exception) {
            Log::error($exception);

        }

        return $this->sendResponse(null, __('authentication::messages.something_wrong'),
            HTTPCode::UNPROCESSABLE_ENTITY, $exception);
    }

    /**
     * @param $userAccount
     * @return UserAccountResource
     */
    private function makeResource($userAccount) {

        return new UserAccountResource($userAccount);
    }

    /**
     * @param UserAccount $userAccount
     * @return JsonResponse
     */
    public function destroy(UserAccount $userAccount) {
        try {
            $account = Auth::user()->userAccounts()->where('id', $userAccount->id);
            if($account->get()->isNotEmpty()){
                $account->delete();
                return $this->sendResponse(null,
                    __('authentication::messages.deleted', ['module' => 'user account']),
                    HTTPCode::OK);
            }
            return $this->sendResponse(null,
                __('authentication::messages.not_found', ['module' => 'user account']),
                HTTPCode::NOT_FOUND);
        } catch (Exception $exception) {
            Log::error($exception);

            return $this->sendResponse(null, __('authentication::messages.something_wrong'),
                HTTPCode::UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * @param UserAccount            $userAccount
     * @param PartiallyUpdateRequest $request
     * @return JsonResponse
     */
    public function partiallyUpdate(UserAccount $userAccount, PartiallyUpdateRequest $request) {
        $input = $request->all();
        try {
            if ($userAccount->is_verified) {
                Auth::user()->primaryAccount()->update('default', false);
                /** @var Model $userAccount */
                $userAccount->update($input);
                $userAccount->fresh();

                return $this->sendResponse($this->makeResource($userAccount),
                    __('authentication::messages.updated', ['module' => 'user account']),
                    HTTPCode::OK);
            }

            return $this->sendResponse($this->makeResource($userAccount),
                __('authentication::messages.not_verified'),
                HTTPCode::UNPROCESSABLE_ENTITY);
        } catch (Exception $exception) {
            Log::error($exception);
        }

        return $this->sendResponse(null, __('authentication::messages.something_wrong'),
            HTTPCode::UNPROCESSABLE_ENTITY);
    }

}
