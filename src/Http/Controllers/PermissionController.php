<?php

namespace Knovators\Authentication\Http\Controllers;

use Exception;
use Illuminate\Http\Request;

use Illuminate\Routing\Controller;
use Knovators\Authentication\Repository\UserRepository;
use Knovators\Support\Helpers\HTTPCode;
use Knovators\Support\Traits\APIResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Knovators\Authentication\Http\Requests\UserPermissionRequest;
use Knovators\Authentication\Models\User;
use Knovators\Authentication\Repository\PermissionRepository;

/**
 * Class AccountController
 * @package Knovators\Authentication\Http\Controllers
 */
class PermissionController extends Controller
{

    use APIResponse;


    protected $userRepository;
    protected $permissionRepository;
    public function __construct(UserRepository $userRepository,PermissionRepository $permissionRepository) {
        $this->userRepository = $userRepository;
        $this->permissionRepository = $permissionRepository;
    }

    public function assignPermission($user,UserPermissionRequest $request)
    {   
        $inputs = $request->all();
        try {
            
            $connection = 'assignPermission' . config('authentication.db');
            $user = $this->userRepository->find($user);
            $this->$connection($user,$inputs['routes']);

            return $this->sendResponse(true,
                trans('authentication::messages.user_permission_added'),
                HTTPCode::CREATED);
        } catch (Exception $exception) {
            Log::error($exception);

            return $this->sendResponse(null, trans('authentication::messages.something_wrong'),
                HTTPCode::UNPROCESSABLE_ENTITY, $exception);
        }
    }

    public function removePermission($user,UserPermissionRequest $request)
    {   
        $inputs = $request->all();
        try {
            
            $connection = 'removePermission' . config('authentication.db');
            $user = $this->userRepository->find($user);
            $this->$connection($user,$inputs['routes']);

            return $this->sendResponse(true,
                trans('authentication::messages.user_permission_added'),
                HTTPCode::CREATED);
        } catch (Exception $exception) {
            Log::error($exception);

            return $this->sendResponse(null, trans('authentication::messages.something_wrong'),
                HTTPCode::UNPROCESSABLE_ENTITY, $exception);
        }
    }

    
    /**
     * @param $user
     * @param $role
     * @throws Exception
     */
    private function assignPermissionMysql($user, $permissions) {
        try {
            $permissionsId = $this->permissionRepository->getIds($permissions,'id');  
            if (empty($permissionsId)){
                return $this->sendResponse(null, trans('authentication::messages.something_wrong'),
                HTTPCode::UNPROCESSABLE_ENTITY);
            }
            $user->permissions()->sync($permissionsId);
        } catch (Exception $exception) {
            throw $exception;
        }
    }

    /**
     * @param $user
     * @param $role
     * @throws Exception
     */
    private function assignPermissionMongodb($user, $permissions) {
        
        try {
            $permissionsId = $this->permissionRepository->getIds($permissions,'_id');  
            if (empty($permissionsId)){
                return $this->sendResponse(null, trans('authentication::messages.something_wrong'),
                HTTPCode::UNPROCESSABLE_ENTITY);
            }
            $user->permissions()->attach($permissionsId);
            $user->save();
        } catch (Exception $exception) {
            throw $exception;
        }
    }


    /**
     * @param $user
     * @param $role
     * @throws Exception
     */
    private function removePermissionMysql($user, $permissions) {
        try {
            $permissionsId = $this->permissionRepository->getIds($permissions,'id');  
            if (empty($permissionsId)){
                return $this->sendResponse(null, trans('authentication::messages.something_wrong'),
                HTTPCode::UNPROCESSABLE_ENTITY);
            }
            $user->permissions()->detach($permissionsId);
        } catch (Exception $exception) {
            throw $exception;
        }
    }

    /**
     * @param $user
     * @param $role
     * @throws Exception
     */
    private function removePermissionMongodb($user, $permissions) {
        try {
            $permissionsId = $this->permissionRepository->getIds($permissions,'_id');  
            if (empty($permissionsId)){
                return $this->sendResponse(null, trans('authentication::messages.something_wrong'),
                HTTPCode::UNPROCESSABLE_ENTITY);
            }
            $user->permissions()->detach($permissionsId);
            $user->save();
        } catch (Exception $exception) {
            throw $exception;
        }
    }

}
