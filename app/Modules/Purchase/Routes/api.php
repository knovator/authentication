<?php

use App\Modules\Purchase\Models\PurchaseOrder;

Route::group([
    'prefix'     => 'admin',
    'middleware' => 'auth_active'
],
    function () {
        Route::resource('purchases', 'PurchaseController');
        Route::put('purchases/order/change-status', 'PurchaseController@changeStatus')
             ->name('purchases.change-status');

        Route::get('purchases/{purchase}/deliveries', 'DeliveryController@index')
             ->name('purchases.deliveries.index');

        Route::post('purchases/{purchase}/deliveries', 'DeliveryController@store')
             ->name('purchases.deliveries.create');

    });


Route::group([
    'prefix' => 'admin',
],
    function () {
        Route::get('purchases/orders/export', 'PurchaseController@exportCsv')
             ->name('purchases.export');
    });

Route::model('purchase', PurchaseOrder::class);
