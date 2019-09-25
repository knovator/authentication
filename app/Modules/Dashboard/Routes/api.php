<?php


Route::group([
    'prefix'     => 'admin/dashboard',
    'middleware' => 'auth_active'
],
    function () {
        Route::get('order-analysis', 'DashboardController@orderAnalysis')
             ->name('dashboard.order-analysis');

        Route::get('design-analysis', 'DashboardController@designAnalysis')
             ->name('dashboard.order-analysis');

        Route::get('top-customer/chart', 'DashboardController@topCustomerChart')
             ->name('dashboard.top-customer-chart');

        Route::get('least-used-thread/chart', 'DashboardController@leastUsedThreadChart')
             ->name('dashboard.least-used-thread-chart');
    });
