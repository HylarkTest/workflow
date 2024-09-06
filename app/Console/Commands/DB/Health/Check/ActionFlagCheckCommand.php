<?php

declare(strict_types=1);

namespace App\Console\Commands\DB\Health\Check;

use Illuminate\Support\Collection;
use Actions\Core\ActionDatabaseManager;
use App\Console\Commands\DB\Health\DBHealthCommand;
use App\Console\Commands\DB\Health\ResettableCommand;
use Symfony\Component\Console\Output\OutputInterface;

class ActionFlagCheckCommand extends DBHealthCommand implements ResettableCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:health:action-flag';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'The `is_latest` column should only be set to true
for the latest action belonging to a subject.';

    /**
     * @var \Illuminate\Support\Collection<int, \Actions\Models\Action>
     */
    protected Collection $badTrue;

    /**
     * @var \Illuminate\Support\Collection<int, \Actions\Models\Action>
     */
    protected Collection $badFalse;

    public function reset(): int
    {
        $this->warn('Setting the `is_latest` flag to the correct value on the `actions` table');
        ActionDatabaseManager::syncIsLatest();

        return 0;
    }

    protected function check(OutputInterface $output): int
    {
        /** @var \Illuminate\Database\Eloquent\Collection<int, \Actions\Models\Action> $badFalse */
        $badFalse = ActionDatabaseManager::isLatestJoinQuery()
            ->whereNotNull('max_id')
            ->where('is_latest', false)
            ->get(['id', 'created_at', 'updated_at']);
        $this->badFalse = $badFalse;

        if ($this->badFalse->isNotEmpty()) {
            $message = $this->badFalse->count().' latest actions were found with the latest flag incorrectly set to false.';

            $this->error($message);
            $this->table(['id', 'created_at', 'updated_at'], $this->badFalse);

            $this->report($message);
        }

        /** @var \Illuminate\Database\Eloquent\Collection<int, \Actions\Models\Action> $badTrue */
        $badTrue = ActionDatabaseManager::isLatestJoinQuery()
            ->whereNull('max_id')
            ->where('is_latest', true)
            ->get(['id', 'created_at', 'updated_at']);

        $this->badTrue = $badTrue;

        if ($this->badTrue->isNotEmpty()) {
            $message = $this->badTrue->count().' older actions were found with the latest flag incorrectly set to true.';

            $this->error($message);
            $this->table(['id', 'created_at', 'updated_at'], $this->badTrue);

            $this->report($message);
        }

        if ($this->badFalse->isEmpty() && $this->badTrue->isEmpty()) {
            $this->info('All `is_latest` flags are set correctly in the actions table.');
        }

        return 0;
    }

    protected function numberToFix(): int
    {
        return $this->badFalse->count() + $this->badTrue->count();
    }

    protected function fix(bool $confirmFixes, OutputInterface $output): int
    {
        if ($this->badFalse->isNotEmpty() || $this->badTrue->isNotEmpty()) {
            if (! $confirmFixes || $this->confirm('Would you like to update all incorrectly set actions?')) {
                return $this->reset();
            }
        } else {
            $this->info('No fixes required for the actions table flags.');
        }

        return 0;
    }
}
