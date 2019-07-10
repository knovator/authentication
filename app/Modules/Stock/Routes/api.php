<?php

use App\Modules\Thread\Models\ThreadColor;

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

        Route::get('stocks', 'StockController@index')
             ->name('stocks.index');

        Route::get('stocks/thread-color/{threadColor}/count', 'StockController@threadCount')
             ->name('stocks.count');

        Route::get('stocks/thread-color/{threadColor}/report', 'StockController@threadReport')
             ->name('stocks.report');
    });

Route::model('threadColor', ThreadColor::class);
