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
        /** @var \Planner\Models\TodoList $calendarModel */
        $calendarModel = new (config('planner.models.calendar'));

        $this->createTableForDistribution('events', 'base_id', function (Blueprint $table) use ($calendarModel) {
            $table->uuid('uuid');
            $table->unsignedBigInteger($calendarModel->getForeignKey());
            $table->string('name');
            $table->dateTime('start_at')->nullable();
            $table->dateTime('end_at')->nullable();
            $table->string('timezone');
            $this->jsonOrStringColumn($table, 'attendees')->nullable();
            $table->boolean('is_all_day')->default(false);
            /*
             * Follows the iCalendar specifications for recurrence.
             * https://www.kanzaki.com/docs/ical/recur.html
             */
            $table->text('recurrence_rule')->nullable();
            /*
             * This rule is calculated from the recurrence rule but is necessary
             * for quickly accessing events relevant to a specific date range.
             */
            $table->dateTime('repeat_until')->nullable();
            $table->text('description')->nullable();
            $table->string('location')->nullable();
            $table->unsignedTinyInteger('priority')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->string('deleted_by')->nullable();
        });

        Schema::table('events', function (Blueprint $table) use ($calendarModel) {
            $table->foreign(['base_id', $calendarModel->getForeignKey()])->references(['base_id', $calendarModel->getKeyName()])->on($calendarModel->getTable())->cascadeOnDelete();
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
