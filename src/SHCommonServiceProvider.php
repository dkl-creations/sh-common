<?php

namespace DklCreations\SHCommon;

use Laravel\Lumen\Application as App;
use DklCreations\SHCommon\Migration\MigrateCommand;
use DklCreations\SHCommon\Migration\RollbackCommand;
use DklCreations\SHCommon\Commands\DbSetCommand;
use DklCreations\SHCommon\Exceptions\Handler;
use DklCreations\SHCommon\Helpers\Config;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Support\ServiceProvider;
use Illuminate\Filesystem\FilesystemServiceProvider;

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
     * Create a new provider instance.
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
        if (!class_exists('Output')) {
            class_alias('DklCreations\SHCommon\Helpers\Output', 'Output');
        }

        // register custom application singlton classes
        $this->registerSingleton();

        // register filesystem
        config(['filesystems.default' => 'sftp']);
        config(['filesystems.disks.sftp' => [
            'driver' => 'sftp',
            'host' => '',
            'username' => '',
            'password' => '',
        ]]);
        $this->app->register(FilesystemServiceProvider::class);

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
