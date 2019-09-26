<?php


Route::group([
    'prefix'     => 'admin/dashboard',
    'middleware' => 'auth_active'
],
    function () {
        Route::get('top-customer/list', 'ReportController@topCustomerList')
             ->name('report.top-customer-list');
    });
