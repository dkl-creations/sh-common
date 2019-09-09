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

    // Custom Fields
    $this->app->router->get('custom-fields', ['uses' => 'CustomFieldController@list']);
    $this->app->router->get('custom-fields/{id:\d+}', ['uses' => 'CustomFieldController@find']);
    $this->app->router->post('custom-fields', ['uses' => 'CustomFieldController@create']);
    $this->app->router->put('custom-fields/{id:\d+}', ['uses' => 'CustomFieldController@update']);
    $this->app->router->delete('custom-fields/{id:\d+}', ['uses' => 'CustomFieldController@delete']);
    $this->app->router->get('custom-fields/list-by-resource', ['uses' => 'CustomFieldController@listByResource']);
    $this->app->router->get('custom-fields/list-by-group', ['uses' => 'CustomFieldController@listByGroup']);
    $this->app->router->put('custom-fields/set-display-order', ['uses' => 'CustomFieldController@setDisplayOrder']);
    $this->app->router->get('custom-fields/file-management', ['uses' => 'CustomFieldController@manageFile']);
    $this->app->router->post('custom-fields/file-management', ['uses' => 'CustomFieldController@manageFile']);
    $this->app->router->delete('custom-fields/file-management', ['uses' => 'CustomFieldController@manageFile']);

});
