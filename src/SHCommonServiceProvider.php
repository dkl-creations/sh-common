<?php

namespace Lewisqic\SHCommon;

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
        require_once(__DIR__ . '/functions.php');
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
