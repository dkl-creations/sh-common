<?php

namespace Lewisqic\SHCommon;

use Lewisqic\SHCommon\Exceptions\Handler;
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

        // bind our custom exception handler
        $this->app->singleton(
            ExceptionHandler::class,
            Handler::class
        );

        // require custom functions file
        require_once(__DIR__ . '/Helpers/functions.php');

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
