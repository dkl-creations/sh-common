<?php

namespace Lewisqic\SHCommon\Middleware;

use Closure;
use Illuminate\Encryption\Encrypter;
use Lewisqic\SHCommon\Helpers\Identity;
use Lewisqic\SHCommon\Helpers\Config;

class LoadDatabase
{

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        if (isset($_SERVER['HTTP_HOST'])) {
            if ( file_exists(base_path('../config_map.php')) ) {
                $config_map = include(base_path('../config_map.php'));
                $host_parts = explode('.', $_SERVER['HTTP_HOST']);
                $service = $host_parts[count($host_parts) - 4];
                $db_database = $config_map['services'][$service]['db_table'];
                if (isset($config_map['db_credentials']['services'][$service])) {
                    $config = $config_map['db_credentials']['services'][$service];
                    $db_username = $config['DB_USERNAME'];
                    $db_password = $config['DB_PASSWORD'];
                }
                if (isset($db_username)) {
                    config(['database.connections.mysql.database' => $db_database]);
                    config(['database.connections.mysql.username' => $db_username]);
                    config(['database.connections.mysql.password' => $db_password]);
                } else {
                    die('Unable to locate database credentials');
                }
            } else {
                die('No config map file found');
            }
        }

        return $next($request);
    }
}