<?php

declare(strict_types=1);

namespace App\Console\Commands\DB\Health\Check;

use App\Console\Commands\DB\Health\DBHealthCommand;
use Symfony\Component\Console\Output\OutputInterface;

class RelationshipMarkerCheckCommand extends DBHealthCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:health:relationship-marker';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Relationship marker options must point to existing
marker groups';

    protected function check(OutputInterface $output): int
    {
        // TODO: Implement check() method.
        $this->warn(' Not yet implemented');

        return 0;
    }

    protected function fix(bool $confirmFixes, OutputInterface $output): int
    {
        // TODO: Implement fix() method.
        return 0;
    }
}
