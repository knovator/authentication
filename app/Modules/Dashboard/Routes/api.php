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

        Route::get('top-customer/chart', 'DashboardController@topCustomerReport')
             ->name('dashboard.top-customer-report');

        Route::get('least-used-thread/chart', 'DashboardController@leastUsedThreadChart')
             ->name('dashboard.least-used-thread-chart');

        Route::get('most-used-designs', 'DashboardController@mostUsedDesign')
             ->name('dashboard.most-used-designs');
    });
