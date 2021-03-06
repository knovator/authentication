<?php

namespace Knovators\Authentication;

use Knovators\Authentication\Commands\MongoDBPassportCommand;
use Knovators\Authentication\Commands\StoreRoutes;
use Knovators\Authentication\Middleware\CheckAccess;
use Knovators\Authentication\Middleware\IsAdmin;
use Knovators\Authentication\Providers\EloquentUserProvider;
use Knovators\Support\PackageServiceProvider;

/**
 * Class AuthServiceProvider
 * @package Knovators\Authentication
 */
class AuthServiceProvider extends PackageServiceProvider
{

    /* -----------------------------------------------------------------
    |  Properties
    | -----------------------------------------------------------------
    */

    /**
     * Package name.
     *
     * @var string
     */
    protected $package = 'authentication';

    /* -----------------------------------------------------------------
     |  Main Methods
     | -----------------------------------------------------------------
     */

    /**
     * Register the service provider.
     */
    public function register() {

        parent::register();

        $this->registerConfig();

        $this->registerProviders([
            Providers\RouteServiceProvider::class,
        ]);

        $this->registerCommands([StoreRoutes::class,MongoDBPassportCommand::class]);

        $this->app->auth->provider('multiple_column', function ($app, array $config) {
            return new EloquentUserProvider($app['hash'], $config['model']);
        });
    }

    /**
     * Boot the service provider.
     */
    public function boot() {
        parent::boot();
        $this->publishConfig();
        $this->loadMigrations();
        $this->publishTranslations();
        $this->app['router']->aliasMiddleware('is_admin', IsAdmin::class);
        $this->app['router']->aliasMiddleware('is_authorized', CheckAccess::class);
    }


}
