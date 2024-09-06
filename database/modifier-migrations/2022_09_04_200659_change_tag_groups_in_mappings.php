<?php

declare(strict_types=1);

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
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
        // Schema::table('mappings', function(Blueprint $table) {
        //     $table->renameColumn('tag_groups', 'marker_groups');
        // });
        DB::table('mappings')
            ->orderBy('id')
            ->each(function ($mapping) {
                $features = json_decode($mapping->features, true);
                if ($features) {
                    $tagFeature = Arr::first($features, fn ($feature) => $feature['id'] === 'TAGS');
                    if ($tagFeature) {
                        array_splice($features, array_search($tagFeature, $features, true), 1);
                        DB::table('mappings')
                            ->where('id', $mapping->id)
                            ->update([
                                'features' => str_replace('{"id":', '{"val":', json_encode($features)),
                                'marker_groups' => json_encode($tagFeature['options']['tags']),
                            ]);
                    }
                }
            });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mappings', function (Blueprint $table) {
            $table->renameColumn('marker_groups', 'tag_groups');
        });
    }
};
