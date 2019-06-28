<?php

namespace DklCreations\SHCommon\Controllers;

use Illuminate\Http\Request;

class MigrationController extends BaseController
{

    /**
     * Run all migrations
     *
     * @param Request $request
     *
     * @return json
     */
    public function runAll(Request $request)
    {

        \Log::debug('run migrations for service: ' . env('APP_SERVICE'));
        \Log::debug($request->all());

        return \Output::message('All migrations have been run')->json();
    }

}
