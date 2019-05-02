<?php

namespace Lewisqic\SHCommon\Commands;

use Illuminate\Console\Command;

class DbSetCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:set {--cmd=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set the organization before executing an artisan command';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $config_map = include(base_path('../config_map.php'));
        $this_service = env('APP_SERVICE');
        $db_name = $config_map['services'][$this_service]['db_name'];
        $all_orgs = $config_map['db_credentials']['organizations'];

        $cmd = $this->option('cmd');
        if (empty($cmd)) {
            $cmd = $this->choice('Which command to run?', [
                'migrate', 'migrate:rollback', 'db:seed'
            ]);
        }

        if (isset($config_map['db_credentials']['services'][$this_service])) {
            $creds = $config_map['db_credentials']['services'][$this_service];

            $this->loadOrgCredentials($db_name, $creds);
            $this->runCommand($cmd);

        } else {
            $orgs = [
                'all'
            ];
            foreach ($all_orgs as $org_name => $creds) {
                $orgs[] = $org_name;
            }
            $org = $this->choice('Which organization?', $orgs);
            if ($org == 'all') {
                foreach ($all_orgs as $org_name => $creds) {
                    $this->loadOrgCredentials($db_name, $creds);
                    $this->runCommand($cmd);
                }
            } else {
                $creds = $config_map['db_credentials']['organizations'][$org];
                $this->loadOrgCredentials($db_name, $creds);
                $this->runCommand($cmd);
            }
        }


    }

    /**
     * Run the specified command now
     */
    protected function runCommand($cmd, $options = [])
    {

        $this->call($cmd, $options);

    }

    /**
     * Load organization credentials into our db config
     *
     * @param $creds
     */
    protected function loadOrgCredentials($db_name, $creds)
    {
        $database = preg_replace('/\{username\}/', $creds['DB_USERNAME'], $db_name);
        config(['database.connections.mysql.database' => $database]);
        config(['database.connections.mysql.username' => $creds['DB_USERNAME']]);
        config(['database.connections.mysql.password' => $creds['DB_PASSWORD']]);
        $this->getLaravel()['db']->purge();
    }

}