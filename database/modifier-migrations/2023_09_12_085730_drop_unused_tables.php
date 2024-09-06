<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use LaravelUtils\Database\KnowsConnection;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    use KnowsConnection;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        foreach ([
            '2019_10_09_142159_create_associates_table' => 'associates',
            '2019_10_09_142219_create_collaborators_table' => 'collaborators',
            '2019_10_09_142116_create_invites_table' => 'invites',
            '2019_10_09_142100_create_grouped_invites_table' => 'grouped_invites',
        ] as $migration => $table) {
            Schema::dropIfExists($table);
            DB::table('migrations')
                ->where('migration', $migration)
                ->delete();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {}
};
