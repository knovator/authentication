<?php

namespace App\Modules\Design\Providers;

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
        $this->loadTranslationsFrom(module_path('design', 'Resources/Lang', 'app'), 'design');
        $this->loadViewsFrom(module_path('design', 'Resources/Views', 'app'), 'design');
        $this->loadMigrationsFrom(module_path('design', 'Database/Migrations', 'app'), 'design');
        $this->loadConfigsFrom(module_path('design', 'Config', 'app'));
        $this->loadFactoriesFrom(module_path('design', 'Database/Factories', 'app'));
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
