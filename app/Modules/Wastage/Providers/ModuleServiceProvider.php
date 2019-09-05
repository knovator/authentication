<?php

namespace App\Modules\Wastage\Providers;

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
        $this->loadTranslationsFrom(module_path('wastage', 'Resources/Lang', 'app'), 'wastage');
        $this->loadViewsFrom(module_path('wastage', 'Resources/Views', 'app'), 'wastage');
        $this->loadMigrationsFrom(module_path('wastage', 'Database/Migrations', 'app'), 'wastage');
        $this->loadConfigsFrom(module_path('wastage', 'Config', 'app'));
        $this->loadFactoriesFrom(module_path('wastage', 'Database/Factories', 'app'));
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
