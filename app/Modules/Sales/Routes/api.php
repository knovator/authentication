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
        // ordered recipes list
        Route::get('sales/{sale}/order-recipes', 'OrderRecipeController@index')
             ->name('sales.recipes.index');

        // Sales delivery
        Route::post('sales/{sale}/deliveries', 'DeliveryController@store')
             ->name('deliveries.create');
    });

Route::model('sale', SalesOrder::class);
