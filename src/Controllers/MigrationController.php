<?php

namespace DklCreations\SHCommon\Controllers;

use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use DklCreations\SHCommon\Helpers\Identity;

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

        $config = get_config_map($request->input('org'));
        $creds = isset($config['db_credentials']) && isset($config['db_credentials'][env('APP_SERVICE')]) ? $config['db_credentials'][env('APP_SERVICE')] : null;
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
