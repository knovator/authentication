<?php

Route::group([
    'prefix'     => 'admin/report',
],
    function () {
        Route::get('top-customer/export', 'ReportController@topCustomerExport')
             ->name('report.top-customer-export');
    });
