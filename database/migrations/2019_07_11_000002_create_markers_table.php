<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    use \CitusLaravel\CitusHelpers;

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $migration = '2019_07_11_000002_create_tags_table';
        if (
            DB::table('migrations')->where('migration', $migration)->exists()
        ) {
            DB::table('migrations')->where('migration', $migration)->delete();

            return;
        }
        $this->createTableForDistribution('markers', 'base_id', function (Blueprint $table) {
            $table->unsignedBigInteger('marker_group_id')->nullable();
            $table->string('name', 255);
            $table->string('color', 31)->nullable();
            $table->unsignedSmallInteger('order');
            $table->timestamps();
            if ($this->usingSqliteConnection()) {
                $table->foreign('marker_group_id')->references('id')->on('marker_groups')->cascadeOnDelete();
            } else {
                $table->foreign(['base_id', 'marker_group_id'])->references(['base_id', 'id'])->on('marker_groups')->cascadeOnDelete();
            }
            $table->index('order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('markers');
    }
};
