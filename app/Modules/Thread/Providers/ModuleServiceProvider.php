<?php

namespace App\Modules\Thread\Providers;

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
        $this->loadTranslationsFrom(module_path('thread', 'Resources/Lang', 'app'), 'thread');
        $this->loadViewsFrom(module_path('thread', 'Resources/Views', 'app'), 'thread');
        $this->loadMigrationsFrom(module_path('thread', 'Database/Migrations', 'app'), 'thread');
        $this->loadConfigsFrom(module_path('thread', 'Config', 'app'));
        $this->loadFactoriesFrom(module_path('thread', 'Database/Factories', 'app'));
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
