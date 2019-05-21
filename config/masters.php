<?php

return [


    'model' => Knovators\Masters\Models\Master::class,

    'resource' => Knovators\Masters\Http\Resources\Master::class,

    'route' => [

        'admin_attributes' => [

            'prefix' => 'api/v1/admin',

            'middleware' => env('MASTER_MIDDLEWARE') ? explode(',',
                env('MASTER_MIDDLEWARE')) : ['api'],
        ],

        'client_attributes' => [

            'prefix' => 'api/v1/masters',

            'middleware' => ['api'],
        ],


    ],


    'delete_relations' => []

];
