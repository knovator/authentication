<?php

namespace Knovators\Authentication\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;
use Knovators\Authentication\Http\Routes\AuthRoute;

/**
 * Class     RouteServiceProvider
 * @package  Knovators\Authentication\Providers
 */
class RouteServiceProvider extends ServiceProvider
{


    protected $namespace = 'Knovators\\Authentication\\Http\\Controllers';


    /* -----------------------------------------------------------------
     |  Main Methods
     | -----------------------------------------------------------------
     */

    /**
     * Define the routes for the application.
     */
    public function map()
    {
        Route::namespace($this->namespace)
            ->group(function () {
                AuthRoute::register();
            });
    }


}
