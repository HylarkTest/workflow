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
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->uuid('uid');
            $table->string('summary');
            $table->timestamp('start_at')->nullable();
            $table->timestamp('end_at')->nullable();
            $table->boolean('is_all_day')->nullable();
            /*
             * Follows the iCalendar specifications for recurrence.
             * https://www.kanzaki.com/docs/ical/recur.html
             */
            $table->string('recurrence_rule')->nullable();
            /*
             * This rule is calculated from the recurrence rule but is necessary
             * for quickly accessing events relevant to a specific date range.
             */
            $table->timestamp('until')->nullable();
            $table->text('description')->nullable();
            $table->string('location')->nullable();
            $table->unsignedTinyInteger('priority')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
