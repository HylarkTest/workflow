<?php

declare(strict_types=1);

namespace App\Console\Commands\DB\Health\Check;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\Console\Helper\ProgressBar;
use App\Console\Commands\DB\Health\DBHealthCommand;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\ConsoleOutputInterface;

class PolymorphicCheckCommand extends DBHealthCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:health:polymorphic
                            {--list : List all the polymorphic columns}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'All polymorphic and columns should correspond to a
table and an id.';

    /**
     * @var \Illuminate\Support\Collection<int, array<int, mixed>>
     */
    protected Collection $headlessIds;

    /**
     * @var \Illuminate\Support\Collection<int, array<int, mixed>>
     */
    protected Collection $invalidMorphTypes;

    protected function check(OutputInterface $output): int
    {
        $tables = DB::table('information_schema.columns')
            ->where('table_schema', $this->getTableSchema())
            ->get(['table_name', 'column_name'])
            ->groupBy('table_name')
            ->map(fn (Collection $columns) => $columns->pluck('column_name'));

        /** @var \Illuminate\Support\Collection<int, array<int, mixed>> $polymorphicColumns */
        $polymorphicColumns = collect();

        $tables->each(static function (Collection $columns, string $table) use (&$polymorphicColumns) {
            $columns->each(static function (string $column) use ($columns, $table, &$polymorphicColumns) {
                if (preg_match('/^(\w+)_type$/', $column, $matches)) {
                    $prefix = $matches[1];
                    $idColumn = $columns->first(fn (string $column) => $column === "{$prefix}_id");

                    if ($idColumn) {
                        $polymorphicColumns[] = [$table, $column, $idColumn];
                    }
                }
            });
        });

        if ($this->option('list')) {
            $this->info('Polymorphic columns found:');
            $this->table(['table', 'type column', 'id column'], $polymorphicColumns);

            return 0;
        }

        $this->headlessIds = collect();
        $this->invalidMorphTypes = collect();

        if ($output instanceof ConsoleOutputInterface) {
            $overallSection = $output->section();
            $tableSection = $output->section();

            $overallBar = new ProgressBar($overallSection, $polymorphicColumns->count());
            $tableBar = new ProgressBar($tableSection);
        } else {
            $overallBar = $this->output->createProgressBar();
            $tableBar = $this->output->createProgressBar();
        }

        if ('\\' !== \DIRECTORY_SEPARATOR || getenv('TERM_PROGRAM') === 'Hyper') {
            $overallBar->setEmptyBarCharacter('░'); // light shade character \u2591
            $overallBar->setProgressCharacter('');
            $overallBar->setBarCharacter('▓'); // dark shade character \u2593
            $tableBar->setEmptyBarCharacter('░'); // light shade character \u2591
            $tableBar->setProgressCharacter('');
            $tableBar->setBarCharacter('▓'); // dark shade character \u2593
        }

        $overallBar->setFormat('%current:2s%/%max:-2s% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s% %memory:6s% %message%');
        $tableBar->setFormat('%current:2s%/%max:-2s% [%bar%] %message%');

        $overallBar->setMessage('Checking the polymorphic tables');
        $tableBar->setMessage('Checking the polymorphic tables');

        foreach ($overallBar->iterate($polymorphicColumns) as $info) {
            [$table, $typeColumn, $idColumn] = $info;
            $overallBar->setMessage("Checking the [[$table]] table");

            $tables = DB::table($table)->groupBy($typeColumn)->pluck($typeColumn);

            foreach ($tableBar->iterate($tables) as $morphTable) {
                $tableBar->setMessage("Checking the ids for the [[$morphTable]] morph type");
                if (! Schema::hasTable($morphTable)) {
                    $this->invalidMorphTypes[] = [$table, $morphTable];

                    continue;
                }
                $headlessIds = DB::table($table)->where($typeColumn, $morphTable)
                    ->leftJoin($morphTable, $morphTable.'.id', '=', $table.'.'.$idColumn)
                    ->whereNull($morphTable.'.id')
                    ->get([$table.'.'.$idColumn, $table.'.created_at', $table.'.updated_at'])
                    ->map(fn ($row) => [$idColumn => $row->{$idColumn}, 'created_at' => $row->created_at, 'updated_at' => $row->updated_at]);

                if ($headlessIds->isNotEmpty()) {
                    $this->headlessIds[] = [$table, $typeColumn, $idColumn, $morphTable, $headlessIds];
                }
            }
        }

        foreach ($this->invalidMorphTypes as [$table, $morphTable]) {
            $this->error("Found polymorphic columns in table [[$table]] with no matching table [[$morphTable]]. This cannot be fixed here.");
        }

        foreach ($this->headlessIds as [$table, $typeColumn, $idColumn, $morphTable, $headlessIds]) {
            $this->error("Found {$headlessIds->count()} polymorphic columns in table [[$table]] without related rows in table [[$morphTable]].");
            $this->table(['id', 'created_at', 'updated_at'], $headlessIds);
        }

        if ($this->invalidMorphTypes->isEmpty() && $this->headlessIds->isEmpty()) {
            $this->info('All polymorphic columns are accounted for.');
        } else {
            if ($this->invalidMorphTypes->isNotEmpty()) {
                $message = 'Found '.$this->invalidMorphTypes->count().' invalid morph types.';
                $this->error($message);

                $this->report($message);
            }
            if ($this->headlessIds->isNotEmpty()) {
                $message = 'In total, found '.$this->headlessIds->pluck('4')->sum->count().' polymorphic ids without related data.';
                $this->error($message);

                $this->report($message);
            }
        }

        return 0;
    }

    protected function numberToFix(): int
    {
        return $this->headlessIds->count();
    }

    protected function fix(bool $confirmFixes, OutputInterface $output): int
    {
        if (! $this->numberToFix()) {
            $this->info('No fixes required for polymorphic columns.');
        }
        $this->headlessIds->each(function ($info) use ($confirmFixes) {
            [$table, $typeColumn, $idColumn, $type, $ids] = $info;

            if ($confirmFixes && ! $this->confirm("Would you like to remove the {$this->headlessIds->count()} headless ids from the [[$table]] table related to [[$type]]?")) {
                $this->warn("Skipping the [[$table]] table.");

                return;
            }
            $this->warn("Removing headless ids from the [[$table]] table related to [[$type]].");

            DB::table($table)->where($typeColumn, $type)->whereIn($idColumn, $ids->pluck($idColumn))->delete();
        });

        return 0;
    }

    private function getTableSchema(): string
    {
        $connection = config('database.default');

        return config(
            key: "database.connections.{$connection}.search_path",
            default: config("database.connections.{$connection}.database")
        );
    }
}
