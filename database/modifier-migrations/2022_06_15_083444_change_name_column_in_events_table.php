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
     *
     * @return void
     */
    public function up()
    {
        /** @var \Planner\Models\TodoList $calendarModel */
        $calendarModel = new (config('planner.models.calendar'));

        Schema::table('events', function (Blueprint $table) use ($calendarModel) {
            $table->unsignedBigInteger($calendarModel->getForeignKey());
            $table->renameColumn('summary', 'name');
            $table->foreign(['base_id', $calendarModel->getForeignKey()])->references(['base_id', $calendarModel->getKeyName()])->on($calendarModel->getTable())->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        /** @var \Planner\Models\TodoList $calendarModel */
        $calendarModel = new (config('planner.models.calendar'));

        Schema::table('events', function (Blueprint $table) use ($calendarModel) {
            $table->dropForeign(['base_id', $calendarModel->getForeignKey()]);
            $table->dropColumn($calendarModel->getForeignKey());
            $table->renameColumn('name', 'summary');
        });
    }
};
