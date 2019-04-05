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
            $site = isset($host_parts[count($host_parts) - 3]) ? $host_parts[count($host_parts) - 3] : null;
            if (isset($config_map[$site])) {
                $config = $config_map[$site];
                foreach ($config as $key => $value) {
                    putenv("{$key}={$value}");
                }
            } else {
                die('Config map key not found');
            }
        } else {
            die('No config map file found');
        }
    }

}