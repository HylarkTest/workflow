<?php

declare(strict_types=1);

namespace App\Console\Commands\DB\Health\Check;

use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Query\JoinClause;
use App\Console\Commands\DB\Health\DBHealthCommand;
use Symfony\Component\Console\Output\OutputInterface;

class SpaceDataCheckCommand extends DBHealthCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:health:space-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ensure there are no relationships between data from different spaces.';

    /**
     * Execute the console command.
     */
    protected function check(OutputInterface $output): int
    {
        $this->info('Checking there are no items related to items from another space...');

        $mixedSpacesInRelationships = DB::table('relationships')
            ->select(['id', 'base_id', 'related_id', 'foreign_id'])
            ->join('items as related_items', function (JoinClause $query) {
                $query->on('related_items.id', 'related_id')
                    ->whereColumn('related_items.base_id', 'relationships.base_id');
            })
            ->join('mappings as related_mappings', function (JoinClause $query) {
                $query->on('related_mappings.id', 'related_items.mapping_id')
                    ->whereColumn('related_mappings.base_id', 'related_items.base_id');
            })
            ->join('items as foreign_items', function (JoinClause $query) {
                $query->on('foreign_items.id', 'foreign_id')
                    ->whereColumn('foreign_items.base_id', 'relationships.base_id');
            })
            ->join('mappings as foreign_mappings', function (JoinClause $query) {
                $query->on('foreign_mappings.id', 'foreign_items.mapping_id')
                    ->whereColumn('foreign_mappings.base_id', 'foreign_items.base_id')
                    ->whereColumn('foreign_mappings.space_id', '<>', 'related_mappings.space_id');
            })->count();

        if ($mixedSpacesInRelationships) {
            $message = "Found [$mixedSpacesInRelationships] related items from different spaces.";
            $this->error($message);
            $this->report($message);
        } else {
            $this->info('All clear');
        }

        $pivotTables = [
            'notes' => ['notable', 'note_id', 'notebooks', 'notebook_id'],
            'todos' => ['todoable', 'todo_id', 'todo_lists', 'todo_list_id'],
            'events' => ['eventable', 'event_id', 'calendars', 'calendar_id'],
            'pins' => ['pinable', 'pin_id', 'pinboards', 'pinboard_id'],
            'links' => ['linkable', 'link_id', 'link_lists', 'link_list_id'],
            'documents' => ['attachable', 'document_id', 'drives', 'drive_id'],
        ];

        foreach ($pivotTables as $parentTable => [$morph, $foreignKey, $listTable, $listKey]) {
            $table = Str::plural($morph);
            $typeColumn = "{$morph}_type";
            $idColumn = "{$morph}_id";

            $types = DB::table($table)->groupBy($typeColumn)
                ->pluck($typeColumn);

            foreach ($types as $type) {
                $this->info("Checking there are no [$morph] models related to [$type] from different spaces...");
                $query = DB::table($table)
                    ->where($typeColumn, $type)
                    ->join("$parentTable as parent", function (JoinClause $query) use ($foreignKey, $table) {
                        $query->on('parent.id', "$table.$foreignKey")
                            ->whereColumn('parent.base_id', "$table.base_id");
                    })
                    ->join("$listTable as parent_list", function (JoinClause $query) use ($listKey) {
                        $query->on('parent_list.id', "parent.$listKey")
                            ->whereColumn('parent_list.base_id', 'parent.base_id');
                    })->join("$type as child", function (JoinClause $query) use ($table, $idColumn) {
                        $query->on("$table.$idColumn", 'child.id')
                            ->whereColumn("$table.base_id", 'child.base_id');
                    });

                if ($type === 'items') {
                    $typeListTable = 'mappings';
                    $typeListKey = 'mapping_id';
                } else {
                    [$_, $_, $typeListTable, $typeListKey] = $pivotTables[$type];
                }

                $query = $query->join("$typeListTable as child_list", function (JoinClause $query) use ($typeListKey) {
                    $query->on("child.$typeListKey", 'child_list.id')
                        ->whereColumn('child.base_id', 'child_list.base_id')
                        ->whereColumn('child_list.space_id', '<>', 'parent_list.space_id');
                });
                $mixedSpacesInPivot = $query->count();

                if ($mixedSpacesInPivot) {
                    $message = "Found [$mixedSpacesInPivot] related [$morph] models from different spaces.";
                    $this->error($message);
                    $this->report($message);
                } else {
                    $this->info('All clear');
                }
            }
        }

        return Command::SUCCESS;
    }

    protected function fix(bool $confirmFixes, OutputInterface $output): int
    {
        $this->warn('This fix has not yet been implemented.');

        return 0;
    }
}
