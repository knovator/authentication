<?php

use App\Modules\Machine\Models\Machine;

Route::group([
    'prefix'     => 'admin',
    'middleware' => 'auth_active'
],
    function () {
        Route::resource('machines', 'MachineController');
        Route::put('machines/partiallyUpdate/{machine}', 'MachineController@partiallyUpdate')
             ->name('machine.partially-update');
    });

Route::group([
    'middleware' => 'auth_active'
],
    function () {
        Route::get('active-machines', 'MachineController@activeMachines')
             ->name('active-machines');
    });


Route::model('machine', Machine::class);
