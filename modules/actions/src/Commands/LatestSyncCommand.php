<?php

declare(strict_types=1);

namespace Actions\Commands;

use Illuminate\Console\Command;
use Actions\Core\ActionDatabaseManager;

class LatestSyncCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'actions:latest:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ensure the `is_latest` column is up to date';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Re-populating the `is_latest` column in `actions`');

        $start = microtime(true);

        ActionDatabaseManager::syncIsLatest();

        $runTime = round(microtime(true) - $start, 2);

        $this->line("Completed ($runTime seconds)");

        return 0;
    }
}
