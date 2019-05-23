<?php


use App\User;

Route::group([
    'prefix'     => 'admin',
    'middleware' => 'auth_active'
],
    function () {
        Route::resource('users', 'UserController');
        Route::put('users/partiallyUpdate/{users}', 'UserController@partiallyUpdate')
             ->name('users.partially-update');
        Route::put('users/{users}/change-password', 'UserController@changePassword')
             ->name('users.change-password');
        Route::put('users/{users}/change-profile', 'UserController@changeProfile')
             ->name('users.change-profile');
    });


Route::model('users', User::class);
