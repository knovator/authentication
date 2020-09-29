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
    'route'         => [
        'auth_attributes'    => [

            'prefix' => 'api/auth',

            'middleware' => env('AUTH_MIDDLEWARE') ? explode(',',
                env('AUTH_MIDDLEWARE')) : [],
        ],
        'account_attributes' => [

            'prefix' => 'api/auth',

            'middleware' => env('AUTH_MIDDLEWARE') ? explode(',',
                env('AUTH_MIDDLEWARE')) : ['api', 'auth_active'],
        ],

        'log_out_attributes' => [

            'prefix' => 'api/auth',

            'middleware' => env('LOG_OUT_MIDDLEWARE') ? explode(',',
                env('LOG_OUT_MIDDLEWARE')) : ['api'],
        ]
    ],
];
