<?php

declare(strict_types=1);

namespace App\Console\Commands\DB\Health\Check;

use App\Models\Item;
use App\Models\Marker;
use App\Models\Markable;
use App\Console\Commands\DB\Health\DBHealthCommand;
use App\Console\Commands\DB\Health\ResettableCommand;
use Symfony\Component\Console\Output\OutputInterface;

class MarkerContextCheckCommand extends DBHealthCommand implements ResettableCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:health:marker-context';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'The `context` column should be set for some markables';

    /**
     * @var \Illuminate\Support\Collection<int, \App\Models\Markable>
     */
    protected $noContext;

    public function reset(): int
    {
        $this->warn('Assigning context to all relevant markables');

        $progressBar = $this->output->createProgressBar(Item::query()->count());

        Item::query()
            ->with('base')
            ->each(function (Item $item) use ($progressBar) {
                $item->base->run(function () use ($item, $progressBar) {
                    $item->markers()
                        ->each(function (Marker $marker) use ($item, $progressBar) {
                            $context = $item->mapping->markerGroups
                                ?->where('group', $marker->marker_group_id)
                                ->first()
                                ?->id();
                            if ($context) {
                                $item->markers()->updateExistingPivot($marker->id, ['context' => $context]);
                            }
                            $progressBar->advance();
                        });
                });
            });

        $progressBar->finish();

        return 0;
    }

    protected function check(OutputInterface $output): int
    {
        $this->noContext = Markable::query()
            ->where('markable_type', 'items')
            ->whereNull('context')
            ->get();

        if ($this->noContext->isNotEmpty()) {
            $message = $this->noContext->count().' markables were found without a context which should have.';

            $this->error($message);
            $this->table(
                ['id', 'markable type', 'markable id', 'created_at', 'updated_at'],
                /** @phpstan-ignore-next-line This is a Larastan bug */
                $this->noContext->select(['id', 'markable_type', 'markable_id', 'created_at', 'updated_at'])
            );

            $this->report($message);
        }

        return 0;
    }

    protected function numberToFix(): int
    {
        return $this->noContext->count();
    }

    protected function fix(bool $confirmFixes, OutputInterface $output): int
    {
        if ($this->noContext->isNotEmpty()) {
            if (! $confirmFixes || $this->confirm('Would you like to update all markers without context?')) {
                return $this->reset();
            }
        } else {
            $this->info('No fixes required for the markables table.');
        }

        return 0;
    }
}
