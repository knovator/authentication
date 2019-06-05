<?php

use App\Models\Master;

return [


    'model' => Master::class,

    'resource' => Knovators\Masters\Http\Resources\Master::class,

    'route' => [

        'admin_attributes' => [

            'prefix' => 'api/v1/admin',

            'middleware' => env('MASTER_MIDDLEWARE') ? explode(',',
                env('MASTER_MIDDLEWARE')) : ['api', 'auth_active'],
        ],

        'client_attributes' => [

            'prefix' => 'api/v1/masters',

            'middleware' => ['api'],
        ],


    ],


    'delete_relations' => [
        'threadColors',
        'childMasters'
    ]

];
