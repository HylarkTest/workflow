<?php

declare(strict_types=1);

use App\Models\Mapping;
use App\Models\MarkerGroup;
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
        Mapping::withoutActions(function () {
            Mapping::query()->eachById(function (Mapping $mapping) {
                $markerGroups = $mapping->markerGroups;
                if ($markerGroups) {
                    /** @var \App\Core\Mappings\Markers\MappingMarkerGroup $markerGroup */
                    foreach ($markerGroups as $markerGroup) {
                        $group = MarkerGroup::query()->find($markerGroup->group);
                        if (! $group) {
                            $mapping->removeMarkerGroup($markerGroup->id);
                        } elseif (strcasecmp($group->name, $markerGroup->name) !== 0) {
                            $markerGroup->name = $group->name;
                            $mapping->base->run(fn () => $mapping->updateMarkerGroup($markerGroup->id, [
                                ...$markerGroup->toArray(),
                                'type' => $markerGroup->type,
                            ]));
                        }
                    }
                }
            });
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {}
};
