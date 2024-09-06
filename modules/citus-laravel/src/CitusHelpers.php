<?php

declare(strict_types=1);

namespace CitusLaravel;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use LaravelUtils\Database\KnowsConnection;

trait CitusHelpers
{
    use KnowsConnection;

    protected function citusInstalled(): bool
    {
        try {
            DB::select('select citus_version()');

            return true;
        } catch (\Exception) {
            return false;
        }
    }

    protected function createDistributedTable(string $table, string $column, string $colocate = 'default'): void
    {
        if ($this->citusInstalled()) {
            $prefix = DB::connection()->getTablePrefix();
            $colocateWith = $colocate === 'default' || $colocate === 'none' ? $colocate : "$prefix$colocate";
            DB::select("select create_distributed_table('$prefix$table', '$column', colocate_with => '$colocateWith')");
        }
    }

    protected function createDistributedTableFromEmpty(string $table, string $column): void
    {
        Schema::rename($table, "old_$table");
        $prefix = DB::connection()->getTablePrefix();
        if ($this->usingPostgresConnection()) {
            DB::statement("CREATE TABLE \"$prefix$table\" (LIKE \"{$prefix}old_$table\" INCLUDING ALL)");
        } else {
            DB::statement("CREATE TABLE $prefix$table LIKE {$prefix}old_$table");
        }
        $this->createDistributedTable($table, $column);
        DB::statement("INSERT INTO \"$prefix$table\" SELECT * FROM \"{$prefix}old_$table\"");
        Schema::drop("old_$table");
    }

    protected function createReferenceTable(string $table): void
    {
        $prefix = DB::connection()->getTablePrefix();
        DB::select("select create_reference_table('$prefix$table')");
    }

    protected function undistributeTable(string $table, bool $cascadeForeignKeys = true): void
    {
        $prefix = DB::connection()->getTablePrefix();
        $cascadeForeignKeysString = $cascadeForeignKeys ? 'true' : 'false';
        DB::select("select undistribute_table('$prefix$table', $cascadeForeignKeysString)");
    }

    protected function createTableForDistribution(string $table, string $distributionColumn, \Closure $cb): void
    {
        Schema::create($table, function (Blueprint $blueprint) use ($distributionColumn, $cb) {
            if ($this->usingPostgresConnection()) {
                $blueprint->unsignedBigInteger('id');
            } else {
                $blueprint->bigIncrements('id');
            }

            $blueprint->foreignId($distributionColumn);

            $cb($blueprint);

            if (! $this->usingPostgresConnection()) {
                $blueprint->index([$distributionColumn, 'id']);
            }
        });

        if ($this->usingPostgresConnection()) {
            $fullTable = DB::connection()->getTablePrefix().$table;
            DB::unprepared("DROP SEQUENCE IF EXISTS \"{$fullTable}_id_seq\"");
            DB::unprepared("CREATE SEQUENCE \"{$fullTable}_id_seq\" OWNED BY $fullTable.id");
            Schema::table($table, function (Blueprint $blueprint) use ($distributionColumn, $fullTable) {
                $blueprint->primary([$distributionColumn, 'id']);
                $blueprint->unsignedBigInteger('id')->default("nextval('{$fullTable}_id_seq'::regclass)")->change();
            });
        }
    }
}
