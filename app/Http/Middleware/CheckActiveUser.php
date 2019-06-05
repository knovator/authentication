<?php

namespace App\Http\Middleware;

use Auth;
use Closure;
use Knovators\Support\Helpers\HTTPCode;
use Knovators\Support\Traits\APIResponse;

/**
 * Class CheckActiveUser
 * @package App\Http\Middleware
 */
class CheckActiveUser
{

    use APIResponse;

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure                 $next
     * @return mixed
     */
    public function handle($request, Closure $next) {
        if (Auth::check() && Auth::user()->isActive()) {
            return $next($request);
        }

        return $this->sendResponse(null,
            __('messages.account_deactivated'),
            HTTPCode::UNAUTHORIZED);
    }
}
