<?php

$this->app->router->group(['prefix' => 'v1', 'middleware' => 'auth-timestamp', 'namespace' => 'DklCreations\SHCommon\Controllers'], function () {

    // identity routes
    $this->app->router->post('identity/user-cache/{id}', ['uses' => 'IdentityController@createUserCache']);
    $this->app->router->put('identity/user-cache/{id}', ['uses' => 'IdentityController@updateUserCache']);
    $this->app->router->delete('identity/user-cache/{id}', ['uses' => 'IdentityController@deleteUserCache']);
    $this->app->router->post('identity/org-config/{id}', ['uses' => 'IdentityController@createOrgConfig']);
    $this->app->router->put('identity/org-config/{id}', ['uses' => 'IdentityController@updateOrgConfig']);
    $this->app->router->delete('identity/org-config/{id}', ['uses' => 'IdentityController@deleteOrgConfig']);

    // migration routes
    $this->app->router->post('migrations/run', ['uses' => 'MigrationController@runMigrations']);

});

$this->app->router->group(['prefix' => 'v1', 'middleware' => 'auth-token', 'namespace' => 'DklCreations\SHCommon\Controllers'], function () {

    // permissions routes
    $this->app->router->get('permissions/service', ['uses' => 'PermissionController@listServicePermissions']);
    $this->app->router->get('permissions/content/assigned', ['uses' => 'PermissionController@assignedContentPermissions']);
    $this->app->router->put('permissions/content/update', ['uses' => 'PermissionController@updateContentPermissions']);

});