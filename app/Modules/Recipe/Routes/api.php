<?php

use App\Modules\Recipe\Models\Recipe;

Route::group([
    'prefix'     => 'admin',
    'middleware' => 'auth_active'
],
    function () {
        Route::resource('recipes', 'RecipeController');
        Route::put('recipes/partiallyUpdate/{recipe}', 'RecipeController@partiallyUpdate')
             ->name('recipes.partially-update');

        Route::get('active-recipes', 'RecipeController@activeRecipes')
             ->name('recipes.active.index');
    });

Route::model('recipe', Recipe::class);
