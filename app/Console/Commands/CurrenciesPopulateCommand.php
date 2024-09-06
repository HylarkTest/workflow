<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Laravel\Telescope\Telescope;
use Illuminate\Support\Facades\DB;
use Mappings\Core\Currency\DatabaseCurrencyRepository;
use Illuminate\Database\Console\Migrations\MigrateCommand;

/**
 * Information about the download files can be found here
 * http://download.geonames.org/export/dump/readme.txt
 */
class CurrenciesPopulateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'currencies:populate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Populate the currencies table with data from Fixer';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        Telescope::stopRecording();
        DB::connection()->unsetEventDispatcher();
        $databaseFile = config('database.connections.currencies.database');
        $this->info('Clearing currencies table');
        if (! file_exists($databaseFile)) {
            touch($databaseFile);
        }
        $this->call(MigrateCommand::class, [
            '--database' => config('mappings.currencies.database'),
            '--path' => 'database/currencies-migrations',
            '--force' => true,
        ]);

        $this->info('Inserting currencies');
        resolve(DatabaseCurrencyRepository::class)->refresh();

        return 0;
    }
}
