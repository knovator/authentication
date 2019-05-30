<?php

use App\Modules\Purchase\Models\PurchaseOrder;

Route::group([
    'prefix'     => 'admin',
    'middleware' => 'auth_active'
],
    function () {
        Route::resource('purchases', 'PurchaseController');
        Route::put('purchases/partiallyUpdate/{purchase}', 'PurchaseController@partiallyUpdate')
             ->name('purchase.partially-update');
    });

Route::model('purchase', PurchaseOrder::class);
