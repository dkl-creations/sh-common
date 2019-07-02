<?php

namespace DklCreations\SHCommon\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class MigrationController extends BaseController
{

    /**
     * Run all migrations
     *
     * @param Request $request
     *
     * @return json
     */
    public function runMigrations(Request $request)
    {
        $org = $request->input('org');
        $global_file = env('CONFIG_MAP');
        $path = pathinfo(base_path($global_file));

        if (!file_exists($path['dirname'] . '/' . $org .'.php')) {
            return;
        }

        $org_config = require($path['dirname'] . '/' . $org .'.php');
        $creds = isset($org_config['db_credentials']) && isset($org_config['db_credentials'][env('APP_SERVICE')]) ? $org_config['db_credentials'][env('APP_SERVICE')] : null;
        if ($creds == null) {
            return;
        }

        // set org connection
        config([
            'database.connections.org' => [
                'driver' => 'mysql',
                'host' => '127.0.0.1',
                'database' => $creds['DB_DATABASE'],
                'username' => $creds['DB_USERNAME'],
                'password' => $creds['DB_PASSWORD'],
            ]
        ]);

        $result = Artisan::call('migrate', [
            '--database' => 'org',
        ]);


        return \Output::message('All migrations have been run')->json();
    }

}
