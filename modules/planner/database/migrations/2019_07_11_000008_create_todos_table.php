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
        Schema::create('todos', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->foreignIdFor(config('planner.models.todo'), 'parent_id')->nullable()->constrained('todos')->cascadeOnDelete();
            $table->foreignIdFor(\Planner\Models\TodoList::class)->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('due_by')->nullable();
            /*
             * Follows the iCalendar specifications for recurrence.
             * https://www.kanzaki.com/docs/ical/recur.html
             * e.g. FREQ=DAILY;INTERVAL=2;FROM=COMPLETION
             * e.g. FREQ=WEEKLY;INTERVAL=1;BYDAY=MO,WE,FR,SA
             */
            $table->string('recurrence')->nullable();
            /*
             * This rule is calculated from the recurrence rule but is necessary
             * for quickly accessing events relevant to a specific date range.
             */
            $table->timestamp('repeat_until')->nullable();
            $table->text('description')->nullable();
            $table->string('location')->nullable();
            $table->unsignedTinyInteger('priority')->default(0);
            $table->string('status')->default('NEEDS_ACTION');
            $table->timestamp('remind_at')->nullable();
            $table->unsignedSmallInteger('order');
            $table->string('color')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('todos');
    }
};
