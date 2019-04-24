<?php

// enter custom routes here
$this->app->router->group(['prefix' => 'v1', 'middleware' => 'auth-cache'], function () {

    $this->app->router->post('identity/cache/create', function() {
        \Log::debug($_POST);

        // delete old cached files
        // create new cache file for the given user
        // file username is an md5 hash or user ID

    });

});