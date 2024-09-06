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
        Schema::create('relationships', static function (Blueprint $table) {
            $table->id();
            $table->string('relation_id');
            $table->foreignIdFor(\App\Models\Item::class, 'related_id')->constrained()->cascadeOnDelete();
            $table->foreignIdFor(\App\Models\Item::class, 'foreign_id')->constrained()->cascadeOnDelete();

            $table->timestamps();

            $table->index(['relation_id', 'related_id']);
            $table->index(['relation_id', 'foreign_id']);

            $table->unique(['relation_id', 'related_id', 'foreign_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('relationships');
    }
};
