<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use LaravelUtils\Database\KnowsConnection;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    use KnowsConnection;

    protected array $tables = [
        'items' => ['start_at', 'due_by'],
        'deadlinables' => ['deadline_time'],
        'events' => ['start_at', 'end_at', 'repeat_until'],
        'todos' => ['due_by', 'repeat_until', 'remind_at'],
        'global_notifications' => ['will_automatically_push_at'],
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if ($this->usingMySqlConnection()) {
            foreach ($this->tables as $table => $columns) {
                Schema::table($table, function (Blueprint $table) use ($columns) {
                    foreach ($columns as $column) {
                        $table->dateTime($column)->change();
                    }
                });
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
