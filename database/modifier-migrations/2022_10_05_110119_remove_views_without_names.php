<?php

declare(strict_types=1);

use Illuminate\Support\Arr;
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
        DB::table('pages')
            ->eachById(function (stdClass $row) {
                $design = $row->design;
                if (! $design) {
                    return;
                }
                $design = json_decode($design, true);
                $views = $design['views'] ?? [];
                $design['views'] = collect($views)
                    ->filter(function (array $view) {
                        return $view['name'] ?? false;
                    })
                    ->map(function (array $view) {
                        $newView = [
                            'name' => $view['name'],
                            'id' => $view['id'],
                            'template' => $view['template'] ?? null,
                            'viewType' => $view['viewType'] ?? null,
                        ];
                        if (isset($view['visibleData'])) {
                            $newView['visibleData'] = collect($view['visibleData'])
                                ->map(function (array $data) {
                                    return Arr::only($data, ['slot', 'combo', 'width', 'formattedId', 'dataType']);
                                });
                        }

                        return $newView;
                    })->values()->all();

                if (isset($design['itemDisplay'])) {
                    $design['itemDisplay'] = collect($design['itemDisplay'])
                        ->map(function (array $display) {
                            $newDisplay = [
                                'header' => $display['header'],
                                'id' => $display['id'],
                            ];
                            if (isset($display['fields'])) {
                                $newDisplay['fields'] = collect($display['fields'])
                                    ->map(function (array $data) {
                                        return Arr::only($data, ['dataType', 'formattedId']);
                                    });
                            }

                            return $newDisplay;
                        })->all();
                }
                DB::table('pages')
                    ->where('id', $row->id)
                    ->update(['design' => $design]);
            });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {}
};
