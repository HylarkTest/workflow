<?php

declare(strict_types=1);

namespace App\Console\Commands\DB\Health\Check;

use Actions\Models\Action;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use App\Console\Commands\DB\Health\DBHealthCommand;
use App\Console\Commands\DB\Health\ResettableCommand;
use Symfony\Component\Console\Output\OutputInterface;

class ActionNameCheckCommand extends DBHealthCommand implements ResettableCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:health:action-name';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'The subject name and performer name should match the subject and performer of the action.';

    /**
     * @var \Illuminate\Support\Collection<int, array>
     */
    protected Collection $badSubjects;

    /**
     * @var \Illuminate\Support\Collection<int, array>
     */
    protected Collection $badPerformers;

    public function reset(): int
    {
        $this->warn('Setting the performer_name and subject_name to match the performer and subject of the action.');
        Action::with(['performer', 'subject'])
            ->eachById(function (Action $action) {
                if ($action->performer) {
                    $action->setPerformer($action->performer);
                }
                if ($action->subject) {
                    $action->setSubject($action->subject);
                }
                $action->timestamps = false;
                $action->save();
            });

        return 0;
    }

    protected function check(OutputInterface $output): int
    {
        $this->badPerformers = collect();
        $this->badSubjects = collect();
        Action::with(['performer', 'subject'])
            ->eachById(function (Action $action) {
                if ($action->performer) {
                    $action->setPerformer($action->performer);
                    if ($action->isDirty('performer_name')) {
                        $this->badPerformers->push($action->only('id', 'subject_type', 'performer_name', 'created_at', 'updated_at'));
                    }
                }
                if ($action->subject) {
                    $action->setSubject($action->subject);
                    if ($action->isDirty('subject_name')) {
                        $this->badSubjects->push(Arr::only($action->getOriginal(), ['id', 'subject_type', 'subject_name', 'created_at', 'updated_at']));
                    }
                }
            });

        if ($this->badPerformers->isNotEmpty()) {
            $message = $this->badPerformers->count().' actions were found with an incorrect performer name.';

            $this->error($message);
            $this->table(['id', 'subject_type', 'performer_name', 'created_at', 'updated_at'], $this->badPerformers);

            $this->report($message);
        }
        if ($this->badSubjects->isNotEmpty()) {
            $message = $this->badSubjects->count().' actions were found with an incorrect subject name.';

            $this->error($message);
            $this->table(['id', 'subject_type', 'subject_name', 'created_at', 'updated_at'], $this->badSubjects);

            $this->report($message);
        }

        if ($this->badSubjects->isEmpty() && $this->badPerformers->isEmpty()) {
            $this->info('All actions have the correct performer and subject names.');
        }

        return 0;
    }

    protected function numberToFix(): int
    {
        return $this->badSubjects->count() + $this->badPerformers->count();
    }

    protected function fix(bool $confirmFixes, OutputInterface $output): int
    {
        if ($this->badSubjects->isNotEmpty() || $this->badPerformers->isNotEmpty()) {
            if (! $confirmFixes || $this->confirm('Would you like to update all incorrectly set actions?')) {
                return $this->reset();
            }
        } else {
            $this->info('No fixes required for the actions table names.');
        }

        return 0;
    }
}
