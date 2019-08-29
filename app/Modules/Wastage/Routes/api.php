<?php

use App\Modules\Wastage\Models\WastageOrder;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your module. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group([
    'prefix'     => 'admin',
    'middleware' => 'auth_active'
],
    function () {
        Route::resource('wastages', 'WastageController');
        Route::put('wastages/order/change-status', 'WastageController@changeStatus')
             ->name('wastages.change-status');


        Route::get('wastages-statuses', 'WastageController@statuses')
             ->name('wastages.statuses');
    });

Route::group([
    'prefix' => 'admin',
],
    function () {
        Route::get('wastages/orders/export', 'WastageController@exportCsv')
             ->name('wastages.export');

        Route::get('wastages/{wastage}/export-summary', 'WastageController@exportSummary')
             ->name('wastages.export.summary');
    });


Route::model('wastage', WastageOrder::class);
