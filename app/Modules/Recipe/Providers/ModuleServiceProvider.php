<?php

namespace App\Modules\Recipe\Providers;

use Caffeinated\Modules\Support\ServiceProvider;

class ModuleServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the module services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadTranslationsFrom(module_path('recipe', 'Resources/Lang', 'app'), 'recipe');
        $this->loadViewsFrom(module_path('recipe', 'Resources/Views', 'app'), 'recipe');
        $this->loadMigrationsFrom(module_path('recipe', 'Database/Migrations', 'app'), 'recipe');
        $this->loadConfigsFrom(module_path('recipe', 'Config', 'app'));
        $this->loadFactoriesFrom(module_path('recipe', 'Database/Factories', 'app'));
    }

    /**
     * Register the module services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->register(RouteServiceProvider::class);
    }
}
