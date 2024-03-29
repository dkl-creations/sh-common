<?php

namespace DklCreations\SHCommon\Helpers;

use Illuminate\Support\Facades\DB;

class Config
{

    /**
     * Load our config map values into our database config
     * @param $dir
     */
    public static function loadDatabaseCredentials($org_data = null)
    {
        if (isset($_SERVER['HTTP_HOST'])) {
            $config_map = get_config_map();

            $host_parts = explode('.', $_SERVER['HTTP_HOST']);
            $service = isset($host_parts[count($host_parts) - 4]) ? $host_parts[count($host_parts) - 4] : '';
            $org_id = $org_data != null ? $org_data['id'] : '';

            $creds = get_db_creds($service, $org_id);
            if ( !empty($creds) ) {
                config(['database.connections.mysql.database' => $creds['DB_DATABASE']]);
                config(['database.connections.mysql.username' => $creds['DB_USERNAME']]);
                config(['database.connections.mysql.password' => $creds['DB_PASSWORD']]);
                DB::purge('mysql');
            }

        }
    }

}