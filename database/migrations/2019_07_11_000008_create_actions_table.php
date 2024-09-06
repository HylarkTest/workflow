<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use LaravelUtils\Database\KnowsConnection;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    use \CitusLaravel\CitusHelpers;
    use KnowsConnection;

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $this->createTableForDistribution('actions', 'base_id', function (Blueprint $table) {
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->nullableMorphs('performer');
            $table->morphs('subject');
            $table->string('performer_name')->nullable();
            $table->string('subject_name')->nullable()->index();
            $table->string('type');
            $table->boolean('is_latest')->default(1);
            $this->nullableJsonOrStringColumn($table, 'payload');
            $table->boolean('is_private')->default(false)->index();
            $table->timestamps(4);

            $table->index('created_at');
            $table->index('updated_at');

            $table->index(['is_latest', 'subject_type', 'subject_id']);
            $table->index(['type', 'subject_type', 'subject_id']);
        });

        Schema::table('actions', function (Blueprint $table) {
            if ($this->usingSqliteConnection()) {
                $table->foreign('parent_id')->references('id')->on('actions')->cascadeOnDelete();
            } else {
                $table->foreign(['base_id', 'parent_id'])->references(['base_id', 'id'])->on('actions')->cascadeOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('actions');
    }
};
