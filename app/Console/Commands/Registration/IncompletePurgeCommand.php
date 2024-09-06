<?php

declare(strict_types=1);

namespace App\Console\Commands\Registration;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Console\Concerns\StoresCronResults;

class IncompletePurgeCommand extends Command
{
    use StoresCronResults;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'registration:incomplete:purge {--dry-run}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Purge all accounts that have not completed the registration process within 30 days.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $query = User::query()
            ->where('created_at', '<', now()->subDays(30)->endOfDay())
            ->whereNull('finished_registration_at');

        $count = $query->count();

        if ($this->option('dry-run')) {
            $this->info("{$count} accounts will be deleted.");

            return 0;
        }

        $deletedCount = 0;

        try {
            $query->each(function (User $user) use (&$deletedCount) {
                DB::beginTransaction();
                $user->forceDelete();
                DB::commit();

                $deletedCount++;
            });
        } catch (\Exception $e) {
            DB::rollBack();

            $this->output->error($e->getMessage());

            return 1;
        } finally {
            $this->output->info('Deleted '.$deletedCount.' accounts.');
            $this->storeCronResult('unfinished_registrations_count', $deletedCount);
        }

        return 0;
    }
}
