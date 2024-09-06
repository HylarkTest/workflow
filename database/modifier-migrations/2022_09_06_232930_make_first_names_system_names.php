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
                $fields = $mapping->fields;
                $fields = preg_replace('/"type":(\s*)"NAME"/', '"type":$1"SYSTEM_NAME"', $fields, 1);
                DB::table('mappings')
                    ->where('id', $mapping->id)
                    ->update(['fields' => $fields]);
            });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {}
};
