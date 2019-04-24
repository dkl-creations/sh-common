<?php

// enter custom routes here
$this->app->router->group(['prefix' => 'v1', 'middleware' => 'auth-cache'], function () {

    $this->app->router->post('identity/cache/create', function() {
        \Log::debug($_POST);


    });

});