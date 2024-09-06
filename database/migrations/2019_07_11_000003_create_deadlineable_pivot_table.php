<?php

declare(strict_types=1);

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
        $this->createTableForDistribution('deadlinables', 'base_id', function (Blueprint $table) {
            $table->morphs('deadlinable');
            $table->unsignedBigInteger('deadline_id');
            $table->dateTime('deadline_time');
            $table->timestamps();

            if ($this->usingSqliteConnection()) {
                $table->foreign('deadline_id')->references('id')->on('deadlines')->cascadeOnDelete();
            } else {
                $table->foreign(['base_id', 'deadline_id'])->references(['base_id', 'id'])->on('deadlines')->cascadeOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deadlinables');
    }
};
