<?php

namespace Lewisqic\SHCommon\Migration;

trait MigrationTrait
{

    /**
     * Load organization credentials into our db config
     *
     * @param $creds
     */
    protected function loadOrgCredentials($creds)
    {
        config(['database.connections.mysql.database' => $creds['database']]);
        config(['database.connections.mysql.username' => $creds['username']]);
        config(['database.connections.mysql.password' => $creds['password']]);
        $this->getLaravel()['db']->purge();
    }

    /**
     * Run migrations in given organizations.
     *
     * @param string $org
     *
     * @return void
     */
    protected function runFor($org = null)
    {
        if ( file_exists(base_path('../config_map.php')) ) {
            $config_map = include(base_path('../config_map.php'));
            $service = env('APP_SERVICE');
            $db_database = $config_map['services'][$service]['db_name'];

            if ($org == 'all') {

                $this->info("\nOrganizations: ALL");

                foreach ($config_map['db_credentials']['organizations'] as $o => $creds) {
                    $this->loadOrgCredentials([
                        'database' => preg_replace('/\{username\}/', $creds['DB_USERNAME'], $db_database),
                        'username' => $creds['DB_USERNAME'],
                        'password' => $creds['DB_PASSWORD'],
                    ]);
                    $this->comment("\nDB User: " . $creds['DB_USERNAME']);
                    parent::handle();
                }

            } elseif ($org != null) {

                $creds = $config_map['sites'][strtolower($org)];

                $creds = $config_map['db_credentials']['organizations'][strtolower($org)];
                $this->loadOrgCredentials([
                    'database' => preg_replace('/\{username\}/', $creds['DB_USERNAME'], $db_database),
                    'username' => $creds['DB_USERNAME'],
                    'password' => $creds['DB_PASSWORD'],
                ]);
                $this->comment("\nOrganization: " . strtoupper($org));
                parent::handle();

            } else {

                if (isset($config_map['db_credentials']['services'][$service])) {
                    $creds = $config_map['db_credentials']['services'][$service];
                    $this->loadOrgCredentials([
                        'database' => $db_database,
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