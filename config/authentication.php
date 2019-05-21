<?php

use App\User;
use Knovators\Authentication\Models\Permission;
use Knovators\Authentication\Models\Role;
use Knovators\Authentication\Http\Resources\User as UserResource;

return [

    'front_url' => env('FRONT_URL'),

    'models' => [

        'user' => User::class,

        'role' => Role::class,

        'permission' => Permission::class

    ],

    'resources' => [

        'user' => UserResource::class,

    ],

    'permission' => [

        'except_modules' => ['log-viewer', 'passport', 'auth']
    ],

    'login_columns' => 'email,phone',

    'route' => [

        'auth_attributes' => [

            'prefix' => 'api/v1/auth',

            'middleware' => env('AUTH_MIDDLEWARE') ? explode(',',
                env('AUTH_MIDDLEWARE')) : ['api'],
        ],

        'log_out_attributes' => [

            'prefix' => 'api/v1/auth',

            'middleware' => env('LOG_OUT_MIDDLEWARE') ? explode(',',
                env('LOG_OUT_MIDDLEWARE')) : ['api','auth:api'],
        ]
    ],

];
