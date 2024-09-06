<?php

declare(strict_types=1);

use App\Models\Page;
use Illuminate\Support\Arr;
use App\Core\Pages\PageType;
use LighthouseHelpers\Utils;
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
        foreach ([
            'todo_lists',
            'calendars',
            'pinboards',
            'notebooks',
            'link_lists',
            'drives',
        ] as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->string('template_refs')->nullable()->after('is_default');
            });
        }

        Page::query()
            ->whereIn('type', Arr::pluck(PageType::listTypes(), 'value'))
            ->whereNotNull('template_refs')
            ->each(function (Page $page) {
                $listIds = $page->lists;
                foreach ($listIds as $id) {
                    try {
                        $list = Utils::resolveModelFromGlobalId($id);
                        $list->template_refs = $page->template_refs;
                        $list->save();
                    } catch (\Exception $e) {
                        // ignore
                    }
                }
            });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        foreach ([
            'todo_lists',
            'calendars',
            'pinboards',
            'notebooks',
            'link_lists',
            'drives',
        ] as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->dropColumn('template_refs');
            });
        }
    }
};
