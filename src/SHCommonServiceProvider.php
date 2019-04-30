<?php

namespace Lewisqic\SHCommon;

use Lewisqic\SHCommon\Migration\MigrateCommand;
use Lewisqic\SHCommon\Migration\RollbackCommand;
use Lewisqic\SHCommon\Exceptions\Handler;
use Lewisqic\SHCommon\Helpers\Config;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Support\ServiceProvider;

/**
 * Class SHCommonServiceProvider
 */
class SHCommonServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {

        // require custom functions file
        require_once(__DIR__ . '/Helpers/functions.php');

        // load database connection if we can
        Config::loadDatabaseCredentials();

        // bind our custom exception handler
        $this->app->singleton(ExceptionHandler::class, Handler::class);

        // update migration command with our own additions
        $this->app->singleton('command.migrate', function ($app) {
            return new MigrateCommand($app['migrator']);
        });

        // update rollback command with our own additions
        $this->app->singleton('command.migrate.rollback', function ($app) {
            return new RollbackCommand($app['migrator']);
        });

        // import any custom routes
        $this->loadRoutesFrom(__DIR__ . '/routes.php');

    }

    /**
     * Register application services
     *
     * @return void
     */
    public function register()
    {

    }
}
