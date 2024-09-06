<?php

declare(strict_types=1);

use App\Models\Item;
use App\Models\Page;
use App\Models\Drive;
use App\Models\Event;
use App\Models\Mapping;
use App\Models\Calendar;
use App\Models\LinkList;
use App\Models\Notebook;
use App\Models\Pinboard;
use App\Models\TodoList;
use Illuminate\Support\Facades\DB;
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
        $models = [
            Notebook::class,
            Pinboard::class,
            LinkList::class,
            TodoList::class,
            Drive::class,
            Mapping::class,
            Item::class,
            Page::class,
            Calendar::class,
            Event::class,
        ];
        foreach ($models as $model) {
            DB::table('actions')
                ->where('subject_type', $model)
                ->update(['subject_type' => (new $model)->getTable()]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $models = [
            Notebook::class,
            Pinboard::class,
            LinkList::class,
            TodoList::class,
            Drive::class,
            Mapping::class,
            Item::class,
            Page::class,
            Calendar::class,
            Event::class,
        ];
        foreach ($models as $model) {
            DB::table('actions')
                ->where('subject_type', (new $model)->getTable())
                ->update(['subject_type' => $model]);
        }
    }
};
