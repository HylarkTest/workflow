<?php

declare(strict_types=1);

namespace App\Console\Commands\DB\Health\Check;

use App\Console\Commands\DB\Health\DBHealthCommand;
use Symfony\Component\Console\Output\OutputInterface;

class APINameCheckCommand extends DBHealthCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:health:api-name';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ensure API names are unique within the domain they
exist and have a URL friendly format.';

    /**
     * TODO: DB health, write validation for API names
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
