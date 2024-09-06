<?php

declare(strict_types=1);

namespace Tests\Markers\Unit;

use Markers\Models\Marker;
use Markers\Models\MarkerGroup;
use Orchestra\Testbench\TestCase;
use Markers\MarkersServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MarkersAutoIncrementTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Adding a marker will automatically increment the order column
     *
     * @test
     */
    public function adding_a_marker_will_automatically_increment_the_order_column(): void
    {
        [$firstMarkerGroup, $secondMarkerGroup] = MarkerGroup::factory(2)->create()->all();

        $firstMarker = $firstMarkerGroup->markers()->save(Marker::factory()->make(['marker_group_id' => null]));
        $secondMarker = $secondMarkerGroup->markers()->save(Marker::factory()->make(['marker_group_id' => null]));
        $thirdMarker = $firstMarkerGroup->markers()->save(Marker::factory()->make(['marker_group_id' => null]));

        static::assertSame(1, $firstMarker->fresh()->order);
        static::assertSame(1, $secondMarker->fresh()->order);
        static::assertSame(2, $thirdMarker->fresh()->order);
    }

    /**
     * A marker group can order the markers all at once
     *
     * @test
     */
    public function a_marker_group_can_order_the_markers_all_at_once(): void
    {
        [$firstMarkerGroup, $secondMarkerGroup] = MarkerGroup::factory(2)->create()->all();

        $firstMarker = $firstMarkerGroup->markers()->save(Marker::factory()->make(['marker_group_id' => null]));
        $otherMarker = $secondMarkerGroup->markers()->save(Marker::factory()->make(['marker_group_id' => null]));
        $secondMarker = $firstMarkerGroup->markers()->save(Marker::factory()->make(['marker_group_id' => null]));
        $thirdMarker = $firstMarkerGroup->markers()->save(Marker::factory()->make(['marker_group_id' => null]));
        $fourthMarker = $firstMarkerGroup->markers()->save(Marker::factory()->make(['marker_group_id' => null]));

        $firstMarkerGroup->orderMarkers([
            $secondMarker->id,
            $firstMarker->id,
            $fourthMarker->id,
            $thirdMarker->id,
        ]);

        static::assertSame(2, $firstMarker->fresh()->order);
        static::assertSame(1, $secondMarker->fresh()->order);
        static::assertSame(4, $thirdMarker->fresh()->order);
        static::assertSame(3, $fourthMarker->fresh()->order);
        static::assertSame(1, $otherMarker->fresh()->order);
    }

    /**
     * Get package providers.
     *
     * @param  \Illuminate\Foundation\Application  $app
     */
    protected function getPackageProviders($app): array
    {
        return [
            MarkersServiceProvider::class,
        ];
    }

    protected function setup(): void
    {
        parent::setup();

        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations/');

        $this->artisan('migrate');
    }
}
