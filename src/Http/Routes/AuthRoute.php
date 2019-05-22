<?php

namespace Knovators\Authentication\Http\Routes;

use Knovators\Support\Routing\RouteRegistrar;

/**
 * Class MasterRoute
 *
 * @package  Knovators\Authentication\Http\Routes
 */
class AuthRoute extends RouteRegistrar
{

    /**
     * Map all routes.
     */
    public function map() {
        $this->group($this->routeAttributes('auth_attributes'), function () {

            $this->name('auth.login')->post('login', 'LoginController@login');

            $this->name('auth.register')->post('register', 'RegisterController@register');

            $this->name('auth.forgot-password')
                 ->post('/forgot-password', 'AuthController@forgotPassword');

            $this->name('auth.reset-password')
                 ->post('/reset-password', 'AuthController@resetPassword');
        });

        $this->group($this->routeAttributes('log_out_attributes'), function () {
            $this->name('auth.logout')->post('logout', 'LoginController@logout');
        });


    }


    /**
     * @param $attributes
     * @return mixed
     */
    public function routeAttributes($attributes) {
        return $this->config('route.' . $attributes, []);
    }


    /**
     * Get config value by key
     *
     * @param string     $key
     * @param mixed|null $default
     *
     * @return mixed
     */
    private function config($key, $default = null) {
        return config("authentication.$key", $default);
    }


}
