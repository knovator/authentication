<?php

use App\Modules\Sales\Models\SalesOrder;

Route::group([
    'prefix'     => 'admin',
    'middleware' => 'auth_active'
],
    function () {
        Route::resource('sales', 'SalesController');
        Route::put('sales/order/change-status', 'SalesController@changeStatus')
             ->name('sales.change-status');
    });

Route::model('sales', SalesOrder::class);
