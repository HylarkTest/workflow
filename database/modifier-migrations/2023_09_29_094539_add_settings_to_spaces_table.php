<?php

declare(strict_types=1);

use App\Models\Space;
use App\Models\Action;
use App\Models\MarkerGroup;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use App\Core\Preferences\SpacePreferences;
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
        if (! Schema::hasColumn('spaces', 'settings')) {
            Schema::table('spaces', function (Blueprint $table) {
                $this->nullableJsonOrStringColumn($table, 'settings');
            });
        }

        Model::withoutTimestamps(function () {
            MarkerGroup::query()
                ->whereNotNull('features')
                ->with(['base.spaces', 'actions'])
                ->each(function (MarkerGroup $markerGroup) {
                    if (! $markerGroup->base) {
                        return;
                    }
                    $spaces = $markerGroup->base->spaces;
                    $features = $markerGroup->getAttributes()['features'] ?? [];
                    $features = $features ? explode(',', $features) : null;
                    $actions = $markerGroup->actions;
                    $featureActions = $actions->filter(function (Action $action) {
                        return $action->payloadHasField('features');
                    });
                    $featureActions->each(function (Action $action) {
                        $action->updatePayloadChanges(
                            function (array $changes, $other, $payload) {
                                return Arr::except($payload, ['features']);
                            }
                        );
                    });
                    $spaces->each(function (Space $space) use ($features, $markerGroup) {
                        $space->updatePreferences(
                            function (SpacePreferences $preferences) use ($markerGroup, $features) {
                                $preferences->markerGroups[$markerGroup->id] = $features;
                            }
                        );
                    });
                });
        });

        Schema::table('marker_groups', function (Blueprint $table) {
            $table->dropColumn('features');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('spaces', function (Blueprint $table) {
            $table->dropColumn('settings');
        });

        Schema::table('marker_groups', function (Blueprint $table) {
            $table->string('features')->nullable();
        });
    }
};
