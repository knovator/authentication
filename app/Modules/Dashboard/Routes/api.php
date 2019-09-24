<?php


Route::group([
    'prefix'     => 'admin/dashboard',
    'middleware' => 'auth_active'
],
    function () {
        Route::get('order-analysis', 'DashboardController@analysis')
             ->name('dashboard.order-analysis');
    });
