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
        $migration = '2019_07_11_000003_create_taggables_table';
        if (
            DB::table('migrations')->where('migration', $migration)->exists()
        ) {
            DB::table('migrations')->where('migration', $migration)->delete();

            return;
        }
        $this->createTableForDistribution('markables', 'base_id', function (Blueprint $table) {
            $table->morphs('markable');
            $table->string('external_id')->nullable();
            $table->string('context')->nullable()->index();
            $table->unsignedBigInteger('marker_id');
            $table->timestamps();
            if ($this->usingSqliteConnection()) {
                $table->foreign('marker_id')->references('id')->on('markers')->cascadeOnDelete();
            } else {
                $table->foreign(['base_id', 'marker_id'])->references(['base_id', 'id'])->on('markers')->cascadeOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('markables');
    }
};
