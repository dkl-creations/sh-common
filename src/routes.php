<?php

// enter custom routes here
$this->app->router->group(['prefix' => 'v1', 'middleware' => 'auth-cache', 'namespace' => 'Lewisqic\SHCommon\Controllers'], function () {

    $this->app->router->post('identity/cache/create', ['uses' => 'IdentityController@createCache']);

});