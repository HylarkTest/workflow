<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use LaravelUtils\Database\KnowsConnection;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    use KnowsConnection;

    protected array $columns = [
        'mappings' => ['features', 'marker_groups'],
        'pages' => ['config', 'design'],
        'actions' => ['payload'],
        'spaces' => ['settings'],
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        foreach ($this->columns as $table => $columns) {
            DB::table($table)
                ->eachById(function ($row) use ($table, $columns) {
                    $update = collect($columns)
                        ->filter(fn ($column) => (bool) $row->$column && str_contains($row->$column, 'ATTACHMENTS'))
                        ->mapWithKeys(fn ($column) => [$column => str_replace('ATTACHMENTS', 'DOCUMENTS', $row->$column)])
                        ->all();
                    if (! $update) {
                        return;
                    }
                    DB::table($table)
                        ->where('id', $row->id)
                        ->update($update);
                });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        foreach ($this->columns as $table => $columns) {
            DB::table($table)
                ->eachById(function ($row) use ($table, $columns) {
                    $update = collect($columns)
                        ->filter(fn ($column) => (bool) $row->$column && str_contains($row->$column, 'DOCUMENTS'))
                        ->mapWithKeys(fn ($column) => [$column => str_replace('DOCUMENTS', 'ATTACHMENTS', $row->$column)])
                        ->all();
                    if (! $update) {
                        return;
                    }
                    DB::table($table)
                        ->where('id', $row->id)
                        ->update($update);
                });
        }
    }
};
