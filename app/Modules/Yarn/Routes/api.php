<?php


Route::group([
    'prefix'     => 'admin',
    'middleware' => 'auth_active'
],
    function () {
        Route::resource('yarns', 'YarnController');
        Route::put('yarns/order/change-status', 'YarnController@changeStatus')
             ->name('yarns.change-status');
    });
