<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use LaravelUtils\Database\KnowsConnection;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    use KnowsConnection;

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('undistributed_actions', function (Blueprint $table) {
            $table->id();
            $table->nullableMorphs('performer');
            $table->morphs('subject');
            $table->string('performer_name')->nullable();
            $table->string('subject_name')->nullable();
            $table->string('type');
            $table->boolean('is_latest')->default(1);
            $this->nullableJsonOrStringColumn($table, 'payload');
            $table->timestamps(4);

            $table->index('created_at');
            $table->index('updated_at');

            $table->index(['is_latest', 'subject_type', 'subject_id']);
            $table->index(['type', 'subject_type', 'subject_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_actions');
    }
};
