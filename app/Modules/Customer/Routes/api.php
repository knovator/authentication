<?php

use App\Modules\Customer\Models\Customer;

Route::group([
    'prefix'     => 'admin',
    'middleware' => 'auth_active'
],
    function () {
        Route::resource('customers', 'CustomerController');
        Route::put('customers/partiallyUpdate/{customer}', 'CustomerController@partiallyUpdate')
             ->name('customers.partially-update');
        Route::get('customer/agents', 'CustomerController@agents')
             ->name('customers.agents.index');
        Route::get('customer/{customer}/ledgers', 'CustomerController@ledgers')
             ->name('customers.ledgers.index');
    });


Route::group([
    'prefix' => 'admin'
],
    function () {
        Route::get('customer/{customer}/export-ledgers', 'CustomerController@exportLedger')
             ->name('customers.ledgers.export');
    });

Route::model('customer', Customer::class);
