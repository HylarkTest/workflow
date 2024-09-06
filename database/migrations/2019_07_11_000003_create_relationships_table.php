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
        $this->createTableForDistribution('relationships', 'base_id', function (Blueprint $table) {
            $table->string('relation_id');
            $table->unsignedBigInteger('related_id');
            $table->unsignedBigInteger('foreign_id');

            $table->timestamps();

            $table->index(['relation_id', 'related_id']);
            $table->index(['relation_id', 'foreign_id']);

            $table->unique(['base_id', 'relation_id', 'related_id', 'foreign_id'], 'relationships_unique');
            if ($this->usingSqliteConnection()) {
                $table->foreign('related_id')->references('id')->on('items')->cascadeOnDelete();
                $table->foreign('foreign_id')->references('id')->on('items')->cascadeOnDelete();
            } else {
                $table->foreign(['base_id', 'related_id'])->references(['base_id', 'id'])->on('items')->cascadeOnDelete();
                $table->foreign(['base_id', 'foreign_id'])->references(['base_id', 'id'])->on('items')->cascadeOnDelete();
            }
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
