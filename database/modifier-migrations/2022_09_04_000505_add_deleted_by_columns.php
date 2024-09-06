<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use LaravelUtils\Database\KnowsConnection;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    use KnowsConnection;

    protected $tables = [
        'spaces',
        'pages',
        'mappings',
        'items',
        'pinboards',
        'pins',
        'notebooks',
        'notes',
        'todo_lists',
        'todos',
        'link_lists',
        'links',
        'calendars',
        'events',
        'drives',
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->string('deleted_by')->nullable()->after('deleted_at');
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
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->dropColumn('deleted_by');
            });
        }
    }
};
