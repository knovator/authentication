<?php

use App\Modules\Design\Models\Design;

Route::group([
    'prefix'     => 'admin',
    'middleware' => 'auth_active'
],
    function () {
        Route::resource('designs', 'DesignController');
        Route::put('designs/partiallyUpdate/{design}', 'DesignController@partiallyUpdate')
             ->name('designs.partially-update');
        Route::put('designs/partiallyApprove/{design}', 'DesignController@partiallyApprove')
             ->name('designs.partially-approve');
        Route::get('designs/export/{design}', 'DesignController@export')
             ->name('designs.export');
    });

Route::group([
    'middleware' => 'auth_active'
],
    function () {
        Route::get('active-designs', 'DesignController@activeDesigns')
             ->name('active-designs');
    });

Route::model('design', Design::class);
