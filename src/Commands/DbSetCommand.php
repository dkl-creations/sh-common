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
    protected $signature = 'db:set {org=all} {--command=}';

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
        $org = $this->argument('org');
        $command = $this->argument('command');

        if (empty($command)) {
            $command = $this->choice('Which command to run?', [
                'migrate', 'migrate:rollback', 'db:seed'
            ]);
        }

        $this->info($command);




    }
}