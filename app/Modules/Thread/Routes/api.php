<?php

use App\Modules\Thread\Models\Thread;

Route::group([
    'prefix'     => 'admin',
    'middleware' => 'auth_active'
],
    function () {
        Route::resource('threads', 'ThreadController');
        Route::put('threads/partiallyUpdate/{thread}', 'ThreadController@partiallyUpdate')
             ->name('thread.partially-update');
    });

Route::model('thread', Thread::class);
