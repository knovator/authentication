<?php


use App\Modules\Yarn\Models\YarnOrder;

Route::group([
    'prefix'     => 'admin',
    'middleware' => 'auth_active'
],
    function () {
        Route::resource('yarns', 'YarnController');
        Route::put('yarns/order/change-status', 'YarnController@changeStatus')
             ->name('yarns.change-status');
    });
Route::group([
    'prefix' => 'admin',
],
    function () {
        Route::get('yarns/orders/export', 'YarnController@exportCsv')
             ->name('yarns.export');
    });


Route::model('yarn', YarnOrder::class);
