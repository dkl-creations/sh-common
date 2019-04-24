<?php

// enter custom routes here
$this->app->router->group(['prefix' => 'v1', 'middleware' => 'auth-cache', 'namespace' => 'Lewisqic\SHCommon\Controllers'], function () {

    $this->app->router->post('identity/cache/{id}', ['uses' => 'IdentityController@createCache']);
    $this->app->router->delete('identity/cache/{id}', ['uses' => 'IdentityController@deleteCache']);

});