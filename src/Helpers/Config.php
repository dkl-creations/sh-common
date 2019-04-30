<?php

namespace Lewisqic\SHCommon\Helpers;

class Config
{

    /**
     * Load our config map values into our database config
     * @param $dir
     */
    public static function loadDatabaseCredentials($org_data = null)
    {
        if (isset($_SERVER['HTTP_HOST'])) {
            if ( file_exists(base_path('../config_map.php')) ) {
                $config_map = include(base_path('../config_map.php'));

                $host_parts = explode('.', $_SERVER['HTTP_HOST']);
                $service = isset($host_parts[count($host_parts) - 4]) ? $host_parts[count($host_parts) - 4] : '';
                $org = $org_data != null ? $org_data['domain'] : '';

                if (isset($config_map['services'][$service]['db_table'])) {
                    $db_database = $config_map['services'][$service]['db_table'];
                    if ( isset($config_map['db_credentials']['services'][$service]) ) {
                        $config = $config_map['db_credentials']['services'][$service];
                        $db_username = $config['DB_USERNAME'];
                        $db_password = $config['DB_PASSWORD'];
                    } else if ( isset($config_map['db_credentials']['organizations'][$org]) ) {
                        $config = $config_map['db_credentials']['organizations'][$org];
                        $db_username = $config['DB_USERNAME'];
                        $db_password = $config['DB_PASSWORD'];
                        $db_database = preg_replace('/\{username\}/', $db_username, $db_database);
                    }

                    if ( isset($db_username) ) {
                        config(['database.connections.mysql.database' => $db_database]);
                        config(['database.connections.mysql.username' => $db_username]);
                        config(['database.connections.mysql.password' => $db_password]);
                    }
                }

            } else {
                die('No config map file found');
            }
        }
    }

}