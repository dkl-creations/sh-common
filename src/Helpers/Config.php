<?php

namespace Lewisqic\SHCommon\Helpers;

class Config
{

    /**
     * Load our config map values into our database config
     * @param $dir
     */
    public static function loadDatabaseCredentials()
    {

        if (isset($_SERVER['HTTP_HOST'])) {
            if ( file_exists(base_path() . '/../config_map.php') ) {
                $config_map = include(base_path() . '/../config_map.php');

                $host_parts = explode('.', $_SERVER['HTTP_HOST']);
                $site = 'jrw'; // update this
                $service = isset($host_parts[count($host_parts) - 3]) ? $host_parts[count($host_parts) - 3] : 'web';

                if ( isset($config_map['services'][$service]) ) {
                    $config = $config_map['services'][$service];
                    $db_database = $config_map['db_names'][$service];
                    $db_username = $config['DB_USERNAME'];
                    $db_password = $config['DB_PASSWORD'];
                } elseif ( isset($config_map['sites'][$site]) ) {
                    $config = $config_map['sites'][$site];
                    $db_database = $config['DB_USERNAME'] . '_' . $config_map['db_names'][$service];
                    $db_username = $config['DB_USERNAME'];
                    $db_password = $config['DB_PASSWORD'];
                }

                if (isset($db_database)) {
                    config(['database.connections.mysql.database' => $db_database]);
                    config(['database.connections.mysql.username' => $db_username]);
                    config(['database.connections.mysql.password' => $db_password]);
                } else {
                    die('Config map key not found');
                }

            } else {
                die('No config map file found');
            }
        }
    }

}