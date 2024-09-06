<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
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
        DB::table('mappings')
            ->orderBy('id')
            ->each(function ($mapping) {
                if ($mapping->features) {
                    DB::table('mappings')
                        ->where('id', $mapping->id)
                        ->update(['features' => str_replace('CALENDAR', 'EVENTS', $mapping->features)]);
                }
            });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {}
};
