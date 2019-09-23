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

        Route::get('sales/{sale}/deliveries', 'DeliveryController@index')
             ->name('deliveries.index');

        Route::post('deliveries/change-status', 'DeliveryController@changeStatus')
             ->name('deliveries.change-status');

        Route::get('sales-thread-analysis', 'SalesController@threadAnalysis')
             ->name('sales.thread.analysis');

        Route::get('manufacturing/companies', 'SalesController@manufacturingCompanies')
             ->name('sales.manufacturing.companies');

        Route::post('send-mail-to-customer/{sale}', 'SalesController@sendMailToCustomer');

        Route::get('sales-statuses', 'SalesController@statuses')
             ->name('sales.statuses');

    });

Route::group([
    'prefix' => 'admin',
],
    function () {
        Route::get('sales/{sale}/deliveries/{delivery}/export-manufacturing',
            'DeliveryController@exportManufacturing')
             ->name('deliveries.export.manufacturing');

        Route::get('sales/{sale}/deliveries/{delivery}/export-accounting',
            'DeliveryController@exportAccounting')
             ->name('deliveries.export.accounting');

        Route::get('sales/{sale}/export-summary', 'SalesController@exportSummary')
             ->name('sales.export.summary');

        Route::get('sales/orders/export', 'SalesController@exportCsv')
             ->name('sales.export');
    });

Route::model('sale', SalesOrder::class);
Route::model('delivery', Delivery::class);
