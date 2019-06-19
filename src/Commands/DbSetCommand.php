<?php

namespace DklCreations\SHCommon\Commands;

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
     * Set the db username to be used
     *
     * @var string
     */
    protected $db_username = '';

    /**
     * Set the db name to be used
     *
     * @var string
     */
    protected $db_database = '';

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
        $config_map = get_config_map();
        $this_service = env('APP_SERVICE');

        $cmd = $this->option('cmd');
        if (empty($cmd)) {
            $cmd = $this->choice('Which command to run?', [
                'migrate',
                'migrate:fresh',
                'migrate:install',
                'migrate:refresh',
                'migrate:reset',
                'migrate:rollback',
                'migrate:status',
                'db:seed',
                'passport:install',
                'passport:keys',
                'passport:client',
                'passport:purge',
            ]);
        }

        $options = $this->ask('Do you want to pass in any options?');

        if (isset($config_map['db_credentials'][$this_service])) {

            $creds = $config_map['db_credentials'][$this_service];
            $this->loadOrgCredentials($creds);
            $this->runCommand($cmd, $options);

        } else {
            $orgs = get_orgs_list();
            array_unshift($orgs, 'all');
            $org = $this->choice('Which organization?', $orgs);
            if ($org == 'all') {
                foreach ($orgs as $org_name) {
                    if ($org_name != 'all') {
                        $config_map = get_config_map($org_name);
                        if ( !isset($config_map['db_credentials'][$this_service]) ) {
                            $this->error('missing database credentials for org/service');
                            die();
                        }
                        $creds = $config_map['db_credentials'][$this_service];
                        $this->loadOrgCredentials($creds);
                        $this->runCommand($cmd, $options);
                    }
                }
            } else {
                $config_map = get_config_map($org);
                if (!isset($config_map['db_credentials'][$this_service])) {
                    $this->error('missing database credentials for org/service');
                    die();
                }
                $creds = $config_map['db_credentials'][$this_service];
                $this->loadOrgCredentials($creds);
                $this->runCommand($cmd, $options);
            }
        }

    }

    /**
     * Run the specified command now
     */
    protected function runCommand($cmd, $options_string = null)
    {
        $options = [];
        if ($options_string != null) {
            $option_parts = explode(' ', $options_string);
            if (is_array($option_parts)) {
                foreach ($option_parts as $option) {
                    $parts = explode('=', $option);
                    $options[$parts[0]] = isset($parts[1]) ? $parts[1] : true;
                }
            }
        }
        $this->question('Running command: "' . strtoupper($cmd . (!empty($options_string) ? ' ' . $options_string : '')) . '" for user: "' . strtoupper($this->db_username) . '" on the database: "' . strtoupper($this->db_database) . '"');
        $this->call($cmd, $options);
    }

    /**
     * Load organization credentials into our db config
     *
     * @param $creds
     */
    protected function loadOrgCredentials($creds)
    {
        $this->db_database = $creds['DB_DATABASE'];
        $this->db_username = $creds['DB_USERNAME'];
        config(['database.connections.mysql.database' => $creds['DB_DATABASE']]);
        config(['database.connections.mysql.username' => $creds['DB_USERNAME']]);
        config(['database.connections.mysql.password' => $creds['DB_PASSWORD']]);
        $this->getLaravel()['db']->purge();
    }

}