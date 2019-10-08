<?php

return [


    'model' => Knovators\Media\Models\Media::class,

    'storage_app_url' => env('APP_URL'),

    'base_folder' => 'uploads',

    'driver' => 'public',

    'route' => [

        'admin_attributes' => [

            'prefix' => 'api/v1/admin/media',

            'middleware' => env('MEDIA_ADMIN_MIDDLEWARE') ? explode(',',
                env('MEDIA_ADMIN_MIDDLEWARE')) : ['api','auth_active'],
        ],

        'client_attributes' => [

            'prefix' => 'api/v1/media',

            'middleware' => env('MEDIA_MIDDLEWARE') ? explode(',',
                env('MEDIA_MIDDLEWARE')) : ['api','auth_active'],
        ],
    ],


    'validate' => [

        'mimes' => 'jpeg,jpg,png,gif',

        'max_size' => '200000',
    ],
    'resize' => [

        'extension' => 'jpg|jpeg|png|gif',
    ],

];
