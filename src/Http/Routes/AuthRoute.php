<?php

namespace Knovators\Authentication\Http\Routes;

use Knovators\Authentication\Models\UserAccount;
use Knovators\Support\Routing\RouteRegister;

/**
 * Class MasterRoute
 *
 * @package  Knovators\Authentication\Http\Routes
 */
class AuthRoute extends RouteRegister
{

    /**
     * Map all routes.
     */
    public function map() {
        $this->group($this->routeAttributes('auth_attributes'), function () {

            $this->name('auth.login')->post('login', 'LoginController@login');

            $this->name('auth.register')->post('{role}/register', 'RegisterController@register')
                 ->where('role', $this->getRoles());

            $this->name('auth.forgot-password')
                 ->post('/forgot-password', 'AuthController@forgotPassword');
            $this->name('auth.resendOtp')
                 ->post('/resend-otp', 'AuthController@resendOtp');

            $this->name('auth.reset-password')
                 ->post('/reset-password', 'AuthController@resetPassword');
            $this->name('auth.verify.post')
                 ->any('/verify-user', 'AuthController@verify');
        });

        $this->group($this->routeAttributes('log_out_attributes'), function () {
            $this->name('auth.logout')->post('logout', 'LoginController@logout');
        });


        $this->group($this->routeAttributes('account_attributes'), function () {
            $this->resource('accounts', 'AccountController');
            $this->name('accounts.partiallyUpdate')
                 ->post('accounts/partiallyUpdate/{account}', 'AccountController@partiallyUpdate');
        });

        $this->group($this->routeAttributes('admin_attributes'), function () {
            $this->name('user.permission.add')->post('user/permission/{user}', 'PermissionController@assignPermission');
            $this->name('user.permission.remove')->post('user/permission/remove/{user}', 'PermissionController@removePermission');
            
        });

        $this->model('account', UserAccount::class);
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

    /**
     * @return mixed
     */
    private function getRoles() {
        return $this->config('roles');
    }


}
