<?php

namespace App\Modules\Sales\Providers;

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
        $this->loadTranslationsFrom(module_path('sales', 'Resources/Lang', 'app'), 'sales');
        $this->loadViewsFrom(module_path('sales', 'Resources/Views', 'app'), 'sales');
        $this->loadMigrationsFrom(module_path('sales', 'Database/Migrations', 'app'), 'sales');
        $this->loadConfigsFrom(module_path('sales', 'Config', 'app'));
        $this->loadFactoriesFrom(module_path('sales', 'Database/Factories', 'app'));
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
