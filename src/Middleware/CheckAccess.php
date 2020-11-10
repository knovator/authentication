<?php

namespace Knovators\Authentication\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Knovators\Authentication\Repository\PermissionRepository;
use Knovators\Authentication\Repository\UserRepository;
use Knovators\Support\Helpers\HTTPCode;
use Knovators\Support\Traits\APIResponse;


/**
 * Class CheckAccess
 * @package Knovators\Authentication\Middleware
 */
class CheckAccess
{

    use APIResponse;

    protected $permissionRepository;
    protected $userRepository;


    /**
     * Permission constructor.
     * @param PermissionRepository $permissionRepository
     */
    public function __construct(
        PermissionRepository $permissionRepository,
        UserRepository $userRepository
    ) {
        $this->permissionRepository = $permissionRepository;
        $this->userRepository = $userRepository;
    }

    public function handle($request, Closure $next) {
        /** @var Request $request */
        
        $routeName = $request->route()->getName();

        if ($routeName) {

            $permission = $this->permissionRepository->findWhere(['route_name'=>$routeName])->first();
            if ($user = Auth::user()){
                //Don't Check if Admin
                $role = $user->orderByRoles()->first();
                $admin = env('ADMIN_CODE') ? explode(',',env('ADMIN_CODE')) : ['ADMIN'] ;
                if ($role &&  !in_array($role->code,$admin)){
                    $userPermissions = $this->userRepository->getPermissions($user,$role);
                    if (!empty($userPermissions) && ! in_array($permission->id , $userPermissions)){
                            return $this->sendResponse(null, trans('authentication::messages.unauthorized_permission'),
                                HTTPCode::UNAUTHORIZED);
                    }
                }
                
            }
        }
        
        return $next($request);
    }

}
