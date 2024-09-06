<?php

declare(strict_types=1);

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
        Schema::table('markables', function (Blueprint $table) {
            $table->string('markable_type')->after('id');
            $table->unsignedBigInteger('markable_id')->after('id');
            $table->index(['markable_type', 'markable_id']);
        });
        DB::table('markables')
            ->update([
                'markable_id' => DB::raw('taggable_id'),
                'markable_type' => DB::raw('taggable_type'),
            ]);
        Schema::table('markables', function (Blueprint $table) {
            $table->dropIndex('taggables_taggable_type_taggable_id_index');
            $table->dropColumn('taggable_type', 'taggable_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('markables', function (Blueprint $table) {
            $table->string('taggable_type')->after('id');
            $table->unsignedBigInteger('taggable_id')->after('id');
            $table->index(['taggable_type', 'taggable_id']);
        });
        DB::table('markables')
            ->update([
                'taggable_id' => DB::raw('markable_id'),
                'taggable_type' => DB::raw('markable_type'),
            ]);
        Schema::table('markables', function (Blueprint $table) {
            $table->dropMorphs('markable');
        });
    }
};
