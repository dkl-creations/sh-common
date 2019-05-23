<?php

namespace Lewisqic\SHCommon;

use Laravel\Lumen\Application as App;
use Lewisqic\SHCommon\Migration\MigrateCommand;
use Lewisqic\SHCommon\Migration\RollbackCommand;
use Lewisqic\SHCommon\Commands\DbSetCommand;
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
     * The lumen application
     *
     * @var \Laravel\Lumen\Application
     */
    protected $app;

    /**
     * Create a new middleware instance.
     *
     * @param  \Laravel\Lumen\Application  $app
     * @return void
     */
    public function __construct(App $app)
    {
        $this->app = $app;
    }

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

        // import any custom routes
        $this->loadRoutesFrom(__DIR__ . '/routes.php');

        // register our custom aliases
        if (!class_exists('Api')) {
            class_alias('Lewisqic\SHCommon\Helpers\Api', 'Api');
        }
        if (!class_exists('Output')) {
            class_alias('Lewisqic\SHCommon\Helpers\Output', 'Output');
        }

        // register custom application singlton classes
        $this->registerSingleton();

    }

    /**
     * Register application services
     *
     * @return void
     */
    public function register()
    {
        $this->commands([
            DbSetCommand::class
        ]);
    }

    public function registerSingleton()
    {
        $this->app->singleton('user', function ($app) {
            return null;
        });
        $this->app->singleton('org', function ($app) {
            return null;
        });
        $this->app->singleton('orgs', function ($app) {
            return null;
        });
        $this->app->singleton('role', function ($app) {
            return null;
        });
        $this->app->singleton('roles', function ($app) {
            return null;
        });
        $this->app->singleton('permissions', function ($app) {
            return null;
        });
    }
}
