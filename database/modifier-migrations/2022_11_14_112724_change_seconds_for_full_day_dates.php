<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use LaravelUtils\Database\KnowsConnection;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    use KnowsConnection;

    protected array $tables = [
        'items' => ['start_at', 'due_by'],
        'events' => ['end_at', 'start_at'],
        'todos' => ['due_by'],
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        foreach ($this->tables as $table => $columns) {
            foreach ($columns as $column) {
                DB::table($table)
                    ->whereTime($column, '23:59:00')
                    ->eachById(
                        fn (\stdClass $row) => DB::table($table)
                            ->where('id', $row->id)
                            ->update([
                                $column => str_replace('23:59:00', '23:59:59', $row->{$column}),
                            ])
                    );
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {}
};
