<?php

namespace Knovators\Authentication\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Knovators\Authentication\Models\User;
use Knovators\Authentication\Repository\PermissionRepository;
use Knovators\Support\Helpers\HTTPCode;
use Knovators\Support\Traits\APIResponse;
use Prettus\Repository\Exceptions\RepositoryException;


/**
 * Class RoutePermission
 * @package Knovators\Authentication\Middleware
 */
class RoutePermission
{

    use APIResponse;

    protected $permissionRepository;


    /**
     * Permission constructor.
     * @param PermissionRepository $permissionRepository
     */
    public function __construct(
        PermissionRepository $permissionRepository
    ) {
        $this->permissionRepository = $permissionRepository;
    }


    /**
     * @param         $request
     * @param Closure $next
     * @return mixed
     * @throws RepositoryException
     */
    public function handle($request, Closure $next) {
        /** @var Request $request */
        $routeName = $request->route()->getName();

        if ($routeName) {

            $permission = $this->permissionRepository->with('roles')
                                                     ->findBy('route_name', $routeName);

            if ($permission && $permission->roles->isNotEmpty() && ($user = Auth::user())) {
                /** @var User $user */
                $role = $user->orderByRoles()->with('permissions')->first();

                if (!$permission->roles->firstWhere('name', $role->name)) {
                    return $this->sendResponse(null, __('messages.unauthorized_permission'),
                        HTTPCode::UNAUTHORIZED);

                }
            }
        }

        return $next($request);
    }

}
