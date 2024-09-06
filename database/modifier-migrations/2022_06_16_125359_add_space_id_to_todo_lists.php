<?php

declare(strict_types=1);

use App\Models\TodoList;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use LaravelUtils\Database\KnowsConnection;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Contracts\Database\Eloquent\Builder;

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
        TodoList::query()->whereDoesntHave('space', function (Builder $query) {
            $query->whereColumn('spaces.base_id', 'todo_lists.base_id');
        })
            ->with('base.spaces')
            ->eachById(function (TodoList $list) {
                $firstSpace = $list->base->spaces->first();
                $list->space()->associate($firstSpace)->save();
            });

        Schema::table('todo_lists', function (Blueprint $table) {
            $table->unsignedBigInteger('space_id')->nullable(false)->change();
        });
        Schema::table('calendars', function (Blueprint $table) {
            $table->unsignedBigInteger('space_id')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('todo_lists', function (Blueprint $table) {
            $table->unsignedBigInteger('space_id')->nullable(true)->change();
        });
        Schema::table('calendars', function (Blueprint $table) {
            $table->unsignedBigInteger('space_id')->nullable(true)->change();
        });
    }
};
