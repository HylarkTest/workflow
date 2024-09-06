<?php

declare(strict_types=1);

namespace App\Console\Commands\DB;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;
use Illuminate\Console\ConfirmableTrait;

class SchemaExportCommand extends Command
{
    use ConfirmableTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:schema:export
                {--connection=mysql : Specify the database connection to export}
                {--c|commit : Commit the file to git}
                {--m|message="Update database schema dump" : The commit message}
    ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export the database structure to a file.
The file is used for efficiently building the test database when testing.

This command is only for local development and should not be run in production.
';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if (! $this->confirmToProceed()) {
            return 5;
        }

        $config = config('database.connections');
        /** @var string $connection */
        $connection = $this->option('connection');

        if (! isset($config[$connection])) {
            $this->error("Connection \"$connection\" is not defined in the database config");

            return 1;
        }

        $config = $config[$connection];

        $exportFile = database_path('schema.sql');

        $dump = $this->executeProcess([
            'mysqldump',
            '--host='.$config['host'],
            '--port='.$config['port'],
            '--user='.$config['username'],
            '--password='.$config['password'],
            '--no-data',
            $config['database'],
        ]);

        file_put_contents($exportFile, $dump);

        $migrationDump = $this->executeProcess([
            'mysqldump',
            '--host='.$config['host'],
            '--port='.$config['port'],
            '--user='.$config['username'],
            '--password='.$config['password'],
            $config['database'],
            'migrations',
        ]);

        file_put_contents($exportFile, $migrationDump, \FILE_APPEND);

        if ($this->option('commit')) {
            /** @var string $message */
            $message = $this->option('message');
            $this->commitChanges($message, $exportFile);
        }

        return 0;
    }

    /**
     * Run a command on the operating system and return the results.
     */
    protected function executeProcess(array $args): string
    {
        return (new Process($args))->mustRun()->getOutput();
    }

    /**
     * Commit a file with a message.
     */
    protected function commitChanges(string $message, ?string $file = null): void
    {
        if ($file) {
            $status = $this->executeProcess(['git', 'status', $file, '--porcelain']);
        } else {
            $status = $this->executeProcess(['git', 'status', '--porcelain']);
        }

        if ($status) {
            $this->executeProcess(['git', 'add', $file ?: '--all']);

            $this->executeProcess(['git', 'commit', "--message=\"$message\""]);
        }
    }
}
