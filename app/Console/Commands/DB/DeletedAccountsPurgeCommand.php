<?php

declare(strict_types=1);

namespace App\Console\Commands\DB;

use App\Models\User;
use Illuminate\Console\Command;

class DeletedAccountsPurgeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:deleted-accounts:purge';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove soft deleted users older than 30 days from the database.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        User::onlyTrashed()
            ->where('deleted_at', '<=', now()->subDays(30))
            ->each(static function (User $user) {
                $user->forceDelete();
            });

        return 0;
    }
}
