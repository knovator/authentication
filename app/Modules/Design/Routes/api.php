<?php

use App\Modules\Design\Models\Design;

Route::group([
    'prefix'     => 'admin',
    'middleware' => 'auth_active'
],
    function () {
        Route::resource('designs', 'DesignController');
        Route::put('designs/partiallyUpdate/{design}', 'DesignController@partiallyUpdate')
             ->name('design.partially-update');
        Route::put('designs/partiallyApprove/{design}', 'DesignController@partiallyApprove')
             ->name('design.partially-approve');
    });

Route::model('design', Design::class);
