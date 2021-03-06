<?php

use Knovators\Authentication\Http\Resources\User as UserResource;
use Knovators\Authentication\Models\Permission;
use Knovators\Authentication\Models\Role;
use Knovators\Authentication\Models\User;

return [

    'front_url'     => env('APP_URL'),
    'db'            => env('DB_CONNECTION', 'mysql'),
    'models'        => [
        'user'       => User::class,
        'role'       => Role::class,
        'permission' => Permission::class
    ],
    'resources'     => [
        'user' => UserResource::class,
    ],
    'permission'    => [
        'except_modules' => ['log-viewer', 'passport', 'auth']
    ],
    'login_columns' => 'email,phone',
    //For use multiple roles please use pipe in between  of them, like user|admin
    'roles'         => 'user',
    'route'         => [
        'auth_attributes'    => [

            'prefix' => 'api/v1/auth',

            'middleware' => env('AUTH_MIDDLEWARE') ? explode(',',
                env('AUTH_MIDDLEWARE')) : [],
        ],
        'account_attributes' => [

            'prefix' => 'api/v1/auth',

            'middleware' => env('AUTH_MIDDLEWARE') ? explode(',',
                env('AUTH_MIDDLEWARE')) : ['api', 'auth_active'],
        ],

        'log_out_attributes' => [

            'prefix' => 'api/v1/auth',

            'middleware' => env('LOG_OUT_MIDDLEWARE') ? explode(',',
                env('LOG_OUT_MIDDLEWARE')) : ['api'],
        ],
        'admin_attributes' => [

            'prefix' => 'api/v1/auth',

            'middleware' => env('ADMIN_MIDDLEWARE') ? explode(',',
                env('ADMIN_MIDDLEWARE')) : ['auth:api','is_admin'],
        ]
    ],
    'user_permission' => env('USER_PERMISSION') ?? false

];
