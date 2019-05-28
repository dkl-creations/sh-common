<?php

$this->app->router->group(['prefix' => 'v1', 'middleware' => 'auth-timestamp', 'namespace' => 'Lewisqic\SHCommon\Controllers'], function () {

    // permissions routes
    $this->app->router->get('permissions/available', ['uses' => 'PermissionController@listAvailablePermissions']);
    $this->app->router->put('permissions/update', ['uses' => 'PermissionController@updateContentPermissions']);

    // identity routes
    $this->app->router->post('identity/cache/{id}', ['uses' => 'IdentityController@createCache']);
    $this->app->router->put('identity/cache/{id}', ['uses' => 'IdentityController@updateCache']);
    $this->app->router->delete('identity/cache/{id}', ['uses' => 'IdentityController@deleteCache']);

});

$this->app->router->group(['prefix' => 'v1', 'middleware' => 'auth-token', 'namespace' => 'Lewisqic\SHCommon\Controllers'], function () {

    // permissions routes
    $this->app->router->put('permissions/update', ['uses' => 'PermissionController@updateContentPermissions']);

});