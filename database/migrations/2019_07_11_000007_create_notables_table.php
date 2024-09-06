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
        $this->createTableForDistribution('notables', 'base_id', function (Blueprint $table) {
            $table->morphs('notable');
            $table->unsignedBigInteger('note_id');
            $table->timestamps();
            if ($this->usingSqliteConnection()) {
                $table->foreign('note_id')->references('id')->on('notes')->cascadeOnDelete();
            } else {
                $table->foreign(['base_id', 'note_id'])->references(['base_id', 'id'])->on('notes')->cascadeOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notables');
    }
};
