<?php

namespace App\Modules\Yarn\Providers;

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
        $this->loadTranslationsFrom(module_path('yarn', 'Resources/Lang', 'app'), 'yarn');
        $this->loadViewsFrom(module_path('yarn', 'Resources/Views', 'app'), 'yarn');
        $this->loadMigrationsFrom(module_path('yarn', 'Database/Migrations', 'app'), 'yarn');
        $this->loadConfigsFrom(module_path('yarn', 'Config', 'app'));
        $this->loadFactoriesFrom(module_path('yarn', 'Database/Factories', 'app'));
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
