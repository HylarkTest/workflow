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
        Schema::create('markers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('marker_group_id')->nullable();
            $table->string('name', 255);
            $table->string('color', 31)->nullable();
            $table->unsignedSmallInteger('order');
            $table->timestamps();

            $table->foreign('marker_group_id')
                ->references('id')
                ->on('marker_groups')
                ->onDelete('CASCADE');
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
