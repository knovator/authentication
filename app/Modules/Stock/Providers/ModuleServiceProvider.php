<?php

namespace App\Modules\Stock\Providers;

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
        $this->loadTranslationsFrom(module_path('stock', 'Resources/Lang', 'app'), 'stock');
        $this->loadViewsFrom(module_path('stock', 'Resources/Views', 'app'), 'stock');
        $this->loadMigrationsFrom(module_path('stock', 'Database/Migrations', 'app'), 'stock');
        $this->loadConfigsFrom(module_path('stock', 'Config', 'app'));
        $this->loadFactoriesFrom(module_path('stock', 'Database/Factories', 'app'));
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
