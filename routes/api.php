<?php


Route::group([
    'prefix'     => 'admin',
    'middleware' => 'auth_active'
],
    function () {
        Route::get('sub-masters/list', 'MasterController@childMasters')
             ->name('masters.childs.index');
    });

Route::get('active/states', 'StateController@activeStates');
