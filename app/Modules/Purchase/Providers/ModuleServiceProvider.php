<?php

namespace App\Modules\Purchase\Providers;

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
        $this->loadTranslationsFrom(module_path('purchase', 'Resources/Lang', 'app'), 'purchase');
        $this->loadViewsFrom(module_path('purchase', 'Resources/Views', 'app'), 'purchase');
        $this->loadMigrationsFrom(module_path('purchase', 'Database/Migrations', 'app'), 'purchase');
        $this->loadConfigsFrom(module_path('purchase', 'Config', 'app'));
        $this->loadFactoriesFrom(module_path('purchase', 'Database/Factories', 'app'));
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
