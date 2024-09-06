<?php

declare(strict_types=1);

use App\Models\Item;
use App\Models\Page;
use App\Models\Marker;
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
        Schema::table('markables', function (Blueprint $table) {
            $table->string('context')->nullable()->index();
        });

        Item::query()
            ->with('base')
            ->each(function (Item $item) {
                $item->base->run(function () use ($item) {
                    $item->markers()
                        ->each(function (Marker $marker) use ($item) {
                            $context = $item->mapping->markerGroups
                                ?->where('group', $marker->marker_group_id)
                                ->first()
                                ?->id();
                            if ($context) {
                                $item->markers()->updateExistingPivot($marker->id, ['context' => $context]);
                            }
                        });
                });
            });

        Page::query()
            ->with('mapping')
            ->where('type', 'ENTITIES')
            ->each(function (Page $page) {
                if ($page->markerFilters) {
                    $markerFilters = $page->markerFilters;
                    foreach ($markerFilters as &$filter) {
                        $markerId = $filter['markerId'];
                        try {
                            $marker = Utils::resolveModelFromGlobalId($markerId);
                            $context = $page->mapping->markerGroups
                                ?->where('group', $marker->marker_group_id)
                                ->first()
                                ?->id();

                            if ($context) {
                                $filter['context'] = $context;
                            }
                        } catch (Exception) {
                            continue;
                        }
                    }
                    $page->markerFilters = $markerFilters;
                    $page->save();
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
        Schema::table('markables', function (Blueprint $table) {
            $table->dropColumn('context');
        });
    }
};
