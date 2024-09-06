<?php

declare(strict_types=1);

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
        Schema::rename('tags', 'markers');
        Schema::rename('tag_groups', 'marker_groups');
        Schema::rename('taggables', 'markables');
        Schema::table('markers', function (Illuminate\Database\Schema\Blueprint $table) {
            $table->renameColumn('tag_group_id', 'marker_group_id');
        });
        Schema::table('markables', function (Illuminate\Database\Schema\Blueprint $table) {
            $table->renameColumn('tag_id', 'marker_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::rename('markers', 'tags');
        Schema::rename('marker_groups', 'tag_groups');
        Schema::rename('markables', 'taggables');
        Schema::table('tags', function (Illuminate\Database\Schema\Blueprint $table) {
            $table->renameColumn('marker_group_id', 'tag_group_id');
        });
        Schema::table('taggables', function (Illuminate\Database\Schema\Blueprint $table) {
            $table->renameColumn('marker_id', 'tag_id');
        });
    }
};
