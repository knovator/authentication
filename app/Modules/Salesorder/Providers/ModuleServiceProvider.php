<?php

namespace App\Modules\Salesorder\Providers;

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
        $this->loadTranslationsFrom(module_path('salesorder', 'Resources/Lang', 'app'), 'salesorder');
        $this->loadViewsFrom(module_path('salesorder', 'Resources/Views', 'app'), 'salesorder');
        $this->loadMigrationsFrom(module_path('salesorder', 'Database/Migrations', 'app'), 'salesorder');
        $this->loadConfigsFrom(module_path('salesorder', 'Config', 'app'));
        $this->loadFactoriesFrom(module_path('salesorder', 'Database/Factories', 'app'));
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
