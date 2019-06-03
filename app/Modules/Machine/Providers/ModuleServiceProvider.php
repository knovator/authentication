<?php

namespace App\Modules\Machine\Providers;

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
        $this->loadTranslationsFrom(module_path('machine', 'Resources/Lang', 'app'), 'machine');
        $this->loadViewsFrom(module_path('machine', 'Resources/Views', 'app'), 'machine');
        $this->loadMigrationsFrom(module_path('machine', 'Database/Migrations', 'app'), 'machine');
        $this->loadConfigsFrom(module_path('machine', 'Config', 'app'));
        $this->loadFactoriesFrom(module_path('machine', 'Database/Factories', 'app'));
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
