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
        /** @var \Planner\Models\Todo $todoModel */
        $todoModel = new (config('planner.models.todo'));
        /** @var \Planner\Models\TodoList $listModel */
        $listModel = new (config('planner.models.todo_list'));

        $this->createTableForDistribution('todos', 'base_id', function (Blueprint $table) use ($listModel) {
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->unsignedBigInteger($listModel->getForeignKey());

            $table->uuid('uuid');
            $table->string('name');
            $table->timestamp('completed_at')->nullable();
            $table->dateTime('due_by')->nullable();
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
            $table->dateTime('repeat_until')->nullable();
            $table->text('description')->nullable();
            $table->string('location')->nullable();
            $table->unsignedTinyInteger('priority')->default(0);
            $table->string('status')->default('NEEDS-ACTION');
            $table->dateTime('remind_at')->nullable();
            $table->unsignedSmallInteger('order');
            $table->string('color')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->string('deleted_by')->nullable();
        });

        Schema::table('todos', function (Blueprint $table) use ($todoModel, $listModel) {
            if ($this->usingSqliteConnection()) {
                $table->foreign('parent_id')->references($todoModel->getKeyName())->on($todoModel->getTable())->cascadeOnDelete();
                $table->foreign($listModel->getForeignKey())->references($listModel->getKeyName())->on($listModel->getTable())->cascadeOnDelete();
            } else {
                $table->foreign(['base_id', 'parent_id'])->references(['base_id', $todoModel->getKeyName()])->on($todoModel->getTable())->cascadeOnDelete();
                $table->foreign(['base_id', $listModel->getForeignKey()])->references(['base_id', $listModel->getKeyName()])->on($listModel->getTable())->cascadeOnDelete();
            }
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
