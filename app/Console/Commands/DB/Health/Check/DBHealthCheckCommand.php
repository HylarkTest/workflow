<?php

declare(strict_types=1);

namespace App\Console\Commands\DB\Health\Check;

use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Finder\Finder;

class DBHealthCheckCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:health:check
                            {--fix : Automatically fix any errors found}
                            {--force : Don\'t ask to fix each error found}
                            {--report : Report the results to support team}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run all the database health check commands.';

    public function handle(): int
    {
        $namespace = app()->getNamespace();

        foreach ((new Finder)->in(__DIR__)->files() as $command) {
            $command = $namespace.str_replace(
                ['/', '.php'],
                ['\\', ''],
                Str::after($command->getPathname(), realpath(app_path()).\DIRECTORY_SEPARATOR)
            );

            if ($command !== __CLASS__
                && is_subclass_of($command, Command::class)
                && ! (new \ReflectionClass($command))->isAbstract()) {
                $this->call(
                    $command,
                    [
                        '--fix' => $this->option('fix'),
                        '--force' => $this->option('force'),
                        '--report' => $this->option('report'),
                    ]
                );
                $this->line("\n\n--------------------------------------------------------------\n\n");
            }
        }

        if ($this->option('report')) {
            Log::channel('check')->info('DB health check completed.');
        }

        return 0;
    }
}
