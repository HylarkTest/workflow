<?php

declare(strict_types=1);

namespace App\Console\Commands\DB\Health;

use Actions\Models\Action;
use Actions\Core\ActionType;
use Illuminate\Console\Command;
use Actions\Core\ActionRecorder;

class ActionIgnoreUpdateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:action:ignore-update
        {--subject=* : Only check actions for a specific model (recommended if the ignored column only exists for certain subjects)}
        {--f|force : Bypass the confirmation dialogue}
    ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove any columns from the actions that should
be ignored';

    public function handle(): int
    {
        if (
            ! $this->option('force')
            && ! $this->confirm('This command is destructive and will permanently remove changes for columns that have been marked as ignored. Are you sure you wish to continue? (Yn)')
        ) {
            return 1;
        }

        $query = Action::query()->whereNotNull('payload')->orderBy('id');

        /** @var array<int, class-string<\Illuminate\Database\Eloquent\Model>|string> $subjectsOption */
        $subjectsOption = $this->option('subject');
        if ($subjectsOption) {
            $subjects = array_map(
                /** @phpstan-ignore-next-line */
                fn (string $subject) => class_exists($subject) ? (new $subject)->getMorphClass() : $subject,
                $subjectsOption
            );

            $query->whereIn('subject_type', $subjects);
        }

        $count = $query->count();

        $bar = $this->output->createProgressBar($count);
        $bar->start();
        $recorder = resolve(ActionRecorder::class);

        $query->eachById(function (Action $action) use ($recorder, $bar) {
            $bar->advance();
            $payload = $action->payload;
            $subjectClass = $action->subjectClass();
            $subjectModel = new $subjectClass;
            if ($action->type->is(ActionType::CREATE())) {
                $payload = $recorder->parsePayload($payload ?? [], $subjectModel);
            } elseif ($action->type->is(ActionType::UPDATE())) {
                $payload = [
                    'changes' => $recorder->parsePayload($payload['changes'] ?? [], $subjectModel),
                    'original' => $recorder->parsePayload($payload['original'] ?? [], $subjectModel),
                ];
            } else {
                return;
            }

            $action->payload = $payload;
            $action->timestamps = false;
            $action->save();
        }, 5000);

        $bar->finish();

        return 0;
    }
}
