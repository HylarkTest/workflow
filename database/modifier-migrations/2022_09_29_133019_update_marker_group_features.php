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
        DB::table('marker_groups')
            ->eachById(function ($row) {
                $features = $row->features;
                if ($features) {
                    $features = preg_replace('/(?<=,|^),/', '', $features);
                }
                DB::table('marker_groups')
                    ->where('id', $row->id)
                    ->update(['features' => $features]);
            });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {}
};
