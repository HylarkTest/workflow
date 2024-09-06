<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ExecutableFinder;

class HylarkWatchCommand extends Command
{
    protected $signature = 'hylark:watch';

    protected $description = 'Watch files on the host machine and reload services on a virtual machine.';

    public function handle(): int
    {
        $watcher = $this->startServerWatcher();

        $this->info('Watching for changes in Application.');

        while (true) {
            if ($watcher->isRunning()
                && $watcher->getIncrementalOutput()) {
                $this->info('Application change detected');

                $this->runCommandOnVM('php ~/code/hylark/artisan octane:reload', 'Restarting Octane');
                $this->runCommandOnVM('php ~/code/hylark/artisan lighthouse:clear-cache', 'Clearing lighthouse cache');
                $this->runCommandOnVM('php ~/code/hylark/artisan horizon:terminate', 'Restarting Horizon');

                $this->info("\tDone");
            } elseif ($watcher->isTerminated()) {
                $this->error(
                    'Watcher process has terminated. Please ensure Node and chokidar are installed.'.\PHP_EOL.
                    $watcher->getErrorOutput()
                );

                return 1;
            }

            usleep(500 * 1000);
        }
    }

    protected function runCommandOnVM(string $command, string $message): void
    {
        $this->warn("\t".$message);
        $process = tap(new Process([
            'ssh', 'vagrant@127.0.0.1', '-p', '2222', $command,
        ]))->enableOutput();

        $process->run();

        if (! $process->isSuccessful()) {
            $this->error($process->getErrorOutput());
        }
    }

    protected function startServerWatcher(): Process
    {
        /** @var array<int, string> $paths */
        $paths = config('octane.watch');
        if (empty($paths)) {
            throw new \InvalidArgumentException('List of directories/files to watch not found. Please update your "config/octane.php" configuration file.');
        }

        return tap(new Process([
            (new ExecutableFinder)->find('node'),
            'file-watcher.cjs',
            json_encode(collect($paths)->map(fn ($path) => base_path($path)), \JSON_THROW_ON_ERROR),
        ], base_path('vendor/laravel/octane/bin'), null, null, null))->start();
    }
}
