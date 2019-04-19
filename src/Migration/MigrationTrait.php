<?php

namespace Lewisqic\SHCommon\Migration;

trait MigrationTrait
{

    /**
     * Load client credentials into our db config
     *
     * @param $creds
     */
    protected function loadClientCredentials($creds)
    {
        config(['database.connections.mysql.database' => $creds['database']]);
        config(['database.connections.mysql.username' => $creds['username']]);
        config(['database.connections.mysql.password' => $creds['password']]);
        $this->getLaravel()['db']->purge();
    }

    /**
     * Run migrations in given clients.
     *
     * @param string $client
     *
     * @return void
     */
    protected function runFor($client = null)
    {
        if ( file_exists(base_path() . '/../config_map.php') ) {
            $service = env('APP_SUBDOMAIN');
            $config_map = include(base_path() . '/../config_map.php');

            if ($client == 'all') {

                $this->info("\nClients: ALL");
                foreach ($config_map['sites'] as $c => $creds) {
                    $this->loadClientCredentials([
                        'database' => $creds['DB_USERNAME'] . '_' . $config_map['db_names'][$service],
                        'username' => $creds['DB_USERNAME'],
                        'password' => $creds['DB_PASSWORD'],
                    ]);
                    $this->comment("\nDB User: " . $creds['DB_USERNAME']);
                    parent::handle();
                }

            } elseif ($client != null) {

                $creds = $config_map['sites'][strtolower($client)];
                $this->loadClientCredentials([
                    'database' => $creds['DB_USERNAME'] . '_' . $config_map['db_names'][$service],
                    'username' => $creds['DB_USERNAME'],
                    'password' => $creds['DB_PASSWORD'],
                ]);
                $this->comment("\nClient: " . strtoupper($client));
                parent::handle();

            } else {

                if (isset($config_map['services'][$service])) {
                    $creds = $config_map['services'][$service];
                    $this->loadClientCredentials([
                        'database' => $config_map['db_names'][$service],
                        'username' => $creds['DB_USERNAME'],
                        'password' => $creds['DB_PASSWORD'],
                    ]);
                    parent::handle();
                } else {
                    $this->error("Unable to locate db credentials for service: {$service}");
                }

            }


        } else {
            $this->error('No config map file found');
        }
    }

}