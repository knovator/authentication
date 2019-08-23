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


        Route::get('yarns-statuses', 'YarnController@statuses')
             ->name('yarns.statuses');

        Route::put('yarns/{yarn}/payment-approve', 'YarnController@updatePayment')
             ->name('yarns.payment-approve');

        Route::post('yarns/send-mail-to-customer/{yarn}', 'YarnController@sendMailToCustomer');
    });
Route::group([
    'prefix' => 'admin',
],
    function () {
        Route::get('yarns/orders/export', 'YarnController@exportCsv')
             ->name('yarns.export');

        Route::get('yarns/{yarn}/export-summary', 'YarnController@exportSummary')
             ->name('yarns.export.summary');
    });


Route::model('yarn', YarnOrder::class);
