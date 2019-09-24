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
    });
