<?php

declare(strict_types=1);

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Laravel\Horizon\Console\SnapshotCommand;
use App\Console\Commands\NotificationsPushCommand;
use App\Console\Commands\CurrenciesPopulateCommand;
use Illuminate\Cache\Console\PruneStaleTagsCommand;
use App\Console\Commands\DB\DeletedAccountsPurgeCommand;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Console\Commands\Registration\IncompletePurgeCommand;
use App\Console\Commands\DB\Health\Check\DBHealthCheckCommand;
use App\Console\Commands\Registration\CompleteEmailSendCommand;
use LaravelUtils\Database\Commands\SoftDeletedModelsPruneCommand;
use Laravel\Telescope\Console\PruneCommand as TelescopePruneCommand;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
    ];

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command(TelescopePruneCommand::class, ['--hours' => config('telescope.prune')])->onOneServer()->daily();
        $schedule->command(SnapshotCommand::class)->onOneServer()->everyFiveMinutes();
        $schedule->command(NotificationsPushCommand::class)->onOneServer()->hourlyAt(5);
        if (app()->environment('production')) {
            $schedule->command(CurrenciesPopulateCommand::class)->onOneServer()->twiceDaily();
        }
        $schedule->command(PruneStaleTagsCommand::class)->onOneServer()->hourly();
        $schedule->command(DeletedAccountsPurgeCommand::class)->onOneServer()->daily();
        $schedule->command(IncompletePurgeCommand::class)->onOneServer()->daily();
        $schedule->command(CompleteEmailSendCommand::class)->onOneServer()->daily();
        $schedule->command(SoftDeletedModelsPruneCommand::class)->onOneServer()->hourly();
        //        $schedule->command(DBHealthCheckCommand::class, ['--report'])->onOneServer()->daily();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
