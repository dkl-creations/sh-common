<?php

namespace Lewisqic\SHCommon\Migration;

use Illuminate\Database\Migrations\Migrator;
use Illuminate\Database\Console\Migrations\MigrateCommand as BaseMigrateCommand;

class MigrateCommand extends BaseMigrateCommand
{
    use MigrationTrait;

    public function __construct(Migrator $migrator)
    {
        $this->signature .= "
                {--all : Run migrations for all available organizations.}
                {--org= : Run migrations for a specific organization.}
        ";
        parent::__construct($migrator);
    }

    /**
     * {@inheritdoc}
     */
    public function handle()
    {
        if ($this->option('all')) {
            $this->runFor('all');
        } elseif ($org = $this->option('org')) {
            $this->runFor($org);
        } else {
            $this->runFor();
        }
    }
}