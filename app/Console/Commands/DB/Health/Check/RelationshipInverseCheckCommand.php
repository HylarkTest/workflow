<?php

declare(strict_types=1);

namespace App\Console\Commands\DB\Health\Check;

use App\Console\Commands\DB\Health\DBHealthCommand;
use Symfony\Component\Console\Output\OutputInterface;

class RelationshipInverseCheckCommand extends DBHealthCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:health:relationship-inverse';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Relationships with an inverse indicated should
have the corresponding relationship on the related mapping';

    /**
     * Execute the console command.
     *
     * TODO: DB health, write logic for checking that relationship inverses exist
     */
    protected function check(OutputInterface $output): int
    {
        // TODO: Implement check() method.
        $this->output->warning('Not yet implemented');

        return 0;
    }

    protected function fix(bool $confirmFixes, OutputInterface $output): int
    {
        // TODO: Implement fix() method.
        return 0;
    }
}
