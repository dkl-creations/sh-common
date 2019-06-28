<?php

$this->app->router->group(['prefix' => 'v1', 'middleware' => 'auth-timestamp', 'namespace' => 'DklCreations\SHCommon\Controllers'], function () {

    // identity routes
    $this->app->router->post('identity/cache/{id}', ['uses' => 'IdentityController@createCache']);
    $this->app->router->put('identity/cache/{id}', ['uses' => 'IdentityController@updateCache']);
    $this->app->router->delete('identity/cache/{id}', ['uses' => 'IdentityController@deleteCache']);

    // migration routes
    $this->app->router->post('migrations/run', ['uses' => 'MigrationController@runMigrations']);

});

$this->app->router->group(['prefix' => 'v1', 'middleware' => 'auth-token', 'namespace' => 'DklCreations\SHCommon\Controllers'], function () {

    // permissions routes
    $this->app->router->get('permissions/service', ['uses' => 'PermissionController@listServicePermissions']);
    $this->app->router->get('permissions/content/assigned', ['uses' => 'PermissionController@assignedContentPermissions']);
    $this->app->router->put('permissions/content/update', ['uses' => 'PermissionController@updateContentPermissions']);

});