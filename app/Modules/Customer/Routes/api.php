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
    });

Route::model('customer', Customer::class);
