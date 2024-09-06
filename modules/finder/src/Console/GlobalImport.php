<?php

declare(strict_types=1);

namespace Finder\Console;

use Illuminate\Console\Command;
use Illuminate\Config\Repository;

class GlobalImport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'finder:import
            {index? : The index that should be imported (Defaults to configuration value: `finder.index`)}
            {--c|chunk= : The number of records to import at a time (Defaults to configuration value: `finder.chunk.searchable`)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import all the globally searchable models into finder';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(Repository $config)
    {
        /** @var string $index */
        $index = $this->argument('index') ?? $config->get('finder.index');
        $classes = $config->get("finder.models.$index");

        foreach ($classes as $class) {
            $class::makeAllGloballySearchable($this->option('chunk'));

            $this->info('All ['.$class.'] records have been imported.');
        }
    }
}
