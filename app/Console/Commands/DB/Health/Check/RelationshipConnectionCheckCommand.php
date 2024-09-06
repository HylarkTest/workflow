<?php

declare(strict_types=1);

namespace App\Console\Commands\DB\Health\Check;

use App\Models\Item;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use App\Console\Commands\DB\Health\DBHealthCommand;
use Symfony\Component\Console\Output\OutputInterface;

class RelationshipConnectionCheckCommand extends DBHealthCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:health:relationship-connection';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'The relationships should all reference a valid
relationship id on the item\'s mappings.';

    /**
     * @var \Illuminate\Support\Collection<int, array>
     */
    protected Collection $invalidRelations;

    protected function check(OutputInterface $output): int
    {
        $this->invalidRelations = Collection::make();

        $query = DB::table('relationships');

        $bar = $this->output->createProgressBar($query->count());

        $query->orderBy('id')->chunk(1000, function (Collection $rows) use ($bar) {
            $itemIds = Arr::pluck($rows, 'related_id');
            /** @var \Illuminate\Database\Eloquent\Collection<int, \Mappings\Models\Item> $items */
            $items = Item::withTrashed()->with('mapping:id,relationships')->findMany($itemIds)->keyBy('id');

            $rows->each(function ($row) use ($items, $bar) {
                /** @var \App\Models\Mapping|null $mapping */
                $mapping = $items[$row->related_id]?->mapping;
                if ($mapping && ! $mapping->relationships->contains('id', $row->relation_id)) {
                    $this->invalidRelations->push([$row->id, $row->relation_id, $mapping->getKey(), $row->related_id]);
                }
                $bar->advance();
            });
        });

        $this->info("\n");

        if ($this->invalidRelations->isNotEmpty()) {
            $message = 'Found '.$this->numberToFix().' invalid relations between some items.';
            $this->error($message);
            $this->table(['pivot_id', 'relation_id', 'mapping_id', 'item_id'], $this->invalidRelations);

            $this->report($message);
        } else {
            $this->info('The relationships are all correct!');
        }

        return 0;
    }

    protected function numberToFix(): int
    {
        return $this->invalidRelations->count();
    }

    protected function fix(bool $confirmFixes, OutputInterface $output): int
    {
        if (! $this->numberToFix()) {
            $this->info('No fixes required for the relations.');
        }

        foreach ($this->invalidRelations as [$pivotId, $relationId, $mappingId]) {
            if ($confirmFixes && ! $this->confirm("Would you like to remove the relationship [[$pivotId]] for the missing relationship [[$relationId]] on mapping [[$mappingId]]?")) {
                $this->error("Not removing the relationship [[$pivotId]].");
            } else {
                $this->warn("Removing the relationship [[$pivotId]].");
                DB::table('relationships')->where('id', $pivotId)->delete();
            }
        }

        return 0;
    }
}
