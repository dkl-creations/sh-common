<?php

namespace Lewisqic\SHCommon\Helpers;

class Config
{

    /**
     * Load our config map values into our environment variables
     * @param $dir
     */
    public static function loadValues($dir)
    {
        if (file_exists($dir . '/../../config_map.php')) {
            $config_map = [];
            require_once($dir . '/../../config_map.php');
            $host_parts = explode('.', $_SERVER['HTTP_HOST']);
            $site = isset($host_parts[count($host_parts) - 3]) ? $host_parts[count($host_parts) - 3] : $host_parts[count($host_parts) - 2];
            $service = isset($host_parts[count($host_parts) - 4]) ? $host_parts[count($host_parts) - 4] : 'web';
            if (isset($config_map['sites'][$site])) {
                $config = $config_map['sites'][$site];
                $db_name = $config_map['db_names'][$service];
                putenv('DB_DATABASE=' . $config['DB_USERNAME'] . '_' . $db_name);
                putenv('DB_USERNAME=' . $config['DB_USERNAME']);
                putenv('DB_PASSWORD=' . $config['DB_PASSWORD']);
            } else {
                die('Config map key (' . $site . ') not found');
            }
        } else {
            die('No config map file found');
        }
    }

}