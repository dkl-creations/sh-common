<?php

namespace Lewisqic\SHCommon\Migration;

use Symfony\Component\Console\Input\InputOption;
use Illuminate\Database\Console\Migrations\RollbackCommand as BaseRollbackCommand;

class RollbackCommand extends BaseRollbackCommand
{
    use MigrationTrait;

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

    /**
     * {@inheritdoc}
     */
    protected function getOptions()
    {
        $options = parent::getOptions();
        array_push($options,
            ['all', null, InputOption::VALUE_NONE, 'Rollback migrations for all organizations.'],
            ['org', null, InputOption::VALUE_OPTIONAL, 'Rollback migrations for a specific organization.']
        );
        return $options;
    }
}