<?php


Route::group([
    'prefix'     => 'admin/report',
    'middleware' => 'auth_active'
],
    function () {
        Route::get('overview', 'ReportController@orderViewReport')
             ->name('report.overview');
    });


Route::group([
    'prefix' => 'admin/report',
],
    function () {
        Route::get('top-customer/export', 'ReportController@topCustomerExport')
             ->name('report.top-customer-export');

        Route::get('least-used-thread/export', 'ReportController@leastUsedThreadExport')
             ->name('report.least-used-thread-export');

    });
