<?php

namespace Knovators\Authentication\Middleware;


use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Knovators\Support\Helpers\HTTPCode;
use Knovators\Support\Traits\APIResponse;


/**
 * Class IsAdmin
 * @package Knovators\Authentication\Middleware
 */
class IsAdmin
{

    use APIResponse;

    /**
     * @param         $request
     * @param Closure $next
     * @return mixed
     * @throws RepositoryException
     */
    public function handle($request, Closure $next) {
        /** @var Request $request */
        
        if (! $user = Auth::user()){
            return $this->sendResponse(null, trans('authentication::messages.unauthorized_permission'),
                            HTTPCode::UNAUTHORIZED);

        }
        $role = $user->orderByRoles()->first();
        $admin = env('ADMIN_CODE') ? explode(',',env('ADMIN_CODE')) : ['ADMIN'] ;
        if ($role && !in_array($role->code,$admin)){
            return $this->sendResponse(null, trans('authentication::messages.unauthorized_permission'),
                            HTTPCode::UNAUTHORIZED);
        }
        
        return $next($request);
    }

}
