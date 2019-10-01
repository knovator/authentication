<?php

use App\Modules\Thread\Models\Thread;

Route::group([
    'prefix'     => 'admin',
    'middleware' => 'auth_active'
],
    function () {
        Route::resource('threads', 'ThreadController');

        Route::put('threads/partiallyUpdate/{thread}', 'ThreadController@partiallyUpdate')
             ->name('threads.partially-update');

        Route::put('thread-colors/partiallyUpdate/{threadColor}',
            'ThreadController@partiallyUpdate')->name('threads.thread-colors-partially-update');

        Route::get('thread/colors-list', 'ThreadController@threadColorsList');
    });

Route::model('thread', Thread::class);
Route::model('threadColor', Thread::class);
