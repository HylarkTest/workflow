<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('deadlinables', static function (Blueprint $table) {
            $table->id();
            $table->morphs('deadlinable');
            $table->unsignedBigInteger('deadline_id');
            $table->timestamp('deadline_time');
            $table->timestamps();

            $table->foreign('deadline_id')->references('id')->on('deadlines');
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
