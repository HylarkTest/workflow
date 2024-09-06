<?php

declare(strict_types=1);

namespace App\Console\Commands\DB\Health\Check;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use App\Console\Commands\DB\Health\DBHealthCommand;
use App\Console\Commands\DB\Health\ResettableCommand;
use Symfony\Component\Console\Output\OutputInterface;

class DeletedUserPrefixCheckCommand extends DBHealthCommand implements ResettableCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:health:deleted-user-prefix';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Soft deleted users should have a prefix of the `deleted_at` timestamp in their email so they can register again.';

    /**
     * @var \Illuminate\Support\Collection<int, array>
     */
    protected Collection $badUsers;

    public function reset(): int
    {
        $this->warn('Adding a prefix to all soft deleted users.');
        DB::table('users')
            ->whereNotNull('deleted_at')
            ->update([
                'email' => DB::raw('CONCAT(deleted_at, email)'),
            ]);

        return 0;
    }

    protected function check(OutputInterface $output): int
    {
        $this->badUsers = DB::table('users')
            ->whereNotNull('deleted_at')
            ->whereNot('email', 'LIKE', DB::raw('CONCAT(deleted_at, \'%\')'))
            ->get(['id', 'name', 'email', 'deleted_at'])
            ->map(fn ($user) => (array) $user);

        if ($this->badUsers->isNotEmpty()) {
            $message = $this->badUsers->count().' deleted users were found without an email prefix.';

            $this->error($message);
            $this->table(['id', 'name', 'email', 'deleted_at'], $this->badUsers);

            $this->report($message);
        } else {
            $this->info('All deleted users have a prefix in their email.');
        }

        return 0;
    }

    protected function numberToFix(): int
    {
        return $this->badUsers->count();
    }

    protected function fix(bool $confirmFixes, OutputInterface $output): int
    {
        if ($this->badUsers->isNotEmpty()) {
            if (! $confirmFixes || $this->confirm('Would you like to update all deleted users without a prefix?')) {
                return $this->reset();
            }
        } else {
            $this->info('No fixes required for the users table emails.');
        }

        return 0;
    }
}
