<?php

use App\Modules\Sales\Models\SalesOrder;
use App\Modules\Sales\Models\Delivery;

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

        Route::put('sales/{sale}/deliveries/{delivery}', 'DeliveryController@update')
             ->name('deliveries.update');

        Route::delete('sales/{sale}/deliveries/{delivery}', 'DeliveryController@destroy')
             ->name('deliveries.destroy');
    });

Route::model('sale', SalesOrder::class);
Route::model('delivery', Delivery::class);
