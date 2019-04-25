<?php

// enter custom routes here
$this->app->router->group(['prefix' => 'v1', 'middleware' => 'auth-identity', 'namespace' => 'Lewisqic\SHCommon\Controllers'], function () {

    $this->app->router->post('identity/cache/{id}', ['uses' => 'IdentityController@createCache']);
    $this->app->router->put('identity/cache/{id}', ['uses' => 'IdentityController@updateCache']);
    $this->app->router->delete('identity/cache/{id}', ['uses' => 'IdentityController@deleteCache']);

});