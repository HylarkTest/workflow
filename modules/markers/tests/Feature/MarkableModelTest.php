<?php

declare(strict_types=1);

namespace Tests\Markers\Feature;

use Markers\Models\Marker;
use Tests\Markers\TestCase;
use Markers\Models\MarkerGroup;
use Markers\Models\MarkableModel;
use Markers\Models\MarkablePivot;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Markers\Models\Concerns\HasAllMarkers;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MarkableModelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A model can have markers
     *
     * @test
     */
    public function a_model_can_have_markers(): void
    {
        $marker = Marker::query()->forceCreate(['name' => 'Marker']);

        /** @var \App\Item $item */
        $item = Item::query()->forceCreate([]);

        $item->markers()->attach($marker);

        static::assertCount(1, $item->markers);
        static::assertTrue($item->markers->first()->is($marker));
        static::assertTrue($item->marker->is($marker));
    }

    /**
     * A model can fetch markers from groups
     *
     * @test
     */
    public function a_model_can_fetch_markers_from_groups(): void
    {
        $group = MarkerGroup::query()->forceCreate(['name' => 'Group']);
        $markerInGroup = Marker::query()->forceCreate(['name' => 'Marker', 'marker_group_id' => $group->id]);
        $markerNotInGroup = Marker::query()->forceCreate(['name' => 'Marker']);

        /** @var \App\Item $item */
        $item = Item::query()->forceCreate([]);

        $item->markers()->attach([$markerInGroup->id, $markerNotInGroup->id]);

        static::assertCount(2, $item->markers);
        static::assertCount(1, $item->markersFromGroup($group)->getResults());
        static::assertTrue($item->markersFromGroup($group)->first()->is($markerInGroup));
    }

    /**
     * Group markers can be eager loaded
     *
     * @test
     */
    public function group_markers_can_be_eager_loaded(): void
    {
        $group = MarkerGroup::query()->forceCreate(['name' => 'Group']);
        $markerInGroup = Marker::query()->forceCreate(['name' => 'Marker', 'marker_group_id' => $group->id]);
        $markerNotInGroup = Marker::query()->forceCreate(['name' => 'Marker']);

        /** @var \App\Item $item */
        $item = Item::query()->forceCreate([]);

        $item->markers()->attach([$markerInGroup->id, $markerNotInGroup->id]);
        $relationName = 'markersFromGroup|'.$group->id;
        $item->loadMarkersFromGroup($group);

        static::assertCount(2, $item->markers);
        static::assertTrue($item->relationLoaded($relationName));
        static::assertCount(1, $item->getMarkersFromGroup($group));
    }

    /**
     * Group markers can be eager loaded on a collection
     *
     * @test
     */
    public function group_markers_can_be_eager_loaded_on_a_collection(): void
    {
        $group = MarkerGroup::query()->forceCreate(['name' => 'Group']);
        $markerInGroup = Marker::query()->forceCreate(['name' => 'Marker', 'marker_group_id' => $group->id]);
        $markerNotInGroup = Marker::query()->forceCreate(['name' => 'Marker']);

        /** @var \App\Item $item */
        $item = Item::query()->forceCreate([]);
        $secondItem = Item::query()->forceCreate([]);

        $item->markers()->attach([$markerInGroup->id, $markerNotInGroup->id]);
        $secondItem->markers()->attach([$markerInGroup->id, $markerNotInGroup->id]);

        $relationName = 'markersFromGroup|'.$group->id;
        $items = Item::query()->get()->loadMarkersFromGroup($group);

        static::assertTrue($items->first()->relationLoaded($relationName));
        static::assertTrue($items->last()->relationLoaded($relationName));
        static::assertCount(1, $items->first()->getMarkersFromGroup($group));
        static::assertCount(1, $items->last()->getMarkersFromGroup($group));
    }

    /**
     * Group markers can be eager loaded on a query
     *
     * @test
     */
    public function group_markers_can_be_eager_loaded_on_a_query(): void
    {
        $group = MarkerGroup::query()->forceCreate(['name' => 'Group']);
        $markerInGroup = Marker::query()->forceCreate(['name' => 'Marker', 'marker_group_id' => $group->id]);
        $markerNotInGroup = Marker::query()->forceCreate(['name' => 'Marker']);

        /** @var \App\Item $item */
        $item = Item::query()->forceCreate([]);
        $secondItem = Item::query()->forceCreate([]);

        $item->markers()->attach([$markerInGroup->id, $markerNotInGroup->id]);
        $secondItem->markers()->attach([$markerInGroup->id, $markerNotInGroup->id]);

        $relationName = 'markersFromGroup|'.$group->id;
        $items = Item::query()->withMarkersFromGroup($group)->get();

        static::assertTrue($items->first()->relationLoaded($relationName));
        static::assertTrue($items->last()->relationLoaded($relationName));
        static::assertCount(1, $items->first()->getMarkersFromGroup($group));
        static::assertCount(1, $items->last()->getMarkersFromGroup($group));
    }

    /**
     * A model can have relationship markers
     *
     * @test
     */
    public function a_model_can_have_relationship_markers(): void
    {
        $marker = Marker::query()->forceCreate(['name' => 'Marker']);

        /** @var \App\Item $item */
        $item = Item::query()->forceCreate([]);
        $relatedItem = Item::query()->forceCreate([]);

        $item->items()->attach($relatedItem, [
            'markers' => $marker,
        ]);

        static::assertCount(1, $relatedItem->reverseItems->first()->pivotMarkers);
        static::assertTrue($relatedItem->reverseItems->first()->pivotMarkers->first()->is($marker));
    }

    /**
     * Markers can be loaded on a relationship
     *
     * @test
     */
    public function markers_can_be_loaded_on_a_relationship(): void
    {
        $marker = Marker::query()->forceCreate(['name' => 'Marker']);

        /** @var \App\Item $item */
        $item = Item::query()->forceCreate([]);
        $relatedItem = Item::query()->forceCreate([]);

        $item->items()->attach($relatedItem, [
            'markers' => $marker,
        ]);
        $item->loadWithMarkers('items');

        static::assertTrue($item->items->first()->pivot->relationLoaded('markers'));
        static::assertCount(1, $item->items->first()->pivot->markers);
    }

    /**
     * Markers can be loaded on a relationship through the query
     *
     * @test
     */
    public function markers_can_be_loaded_on_a_relationship_through_the_query(): void
    {
        $marker = Marker::query()->forceCreate(['name' => 'Marker']);

        /** @var \App\Item $item */
        $item = Item::query()->forceCreate([]);
        $relatedItem = Item::query()->forceCreate([]);

        $item->items()->attach($relatedItem, [
            'markers' => $marker,
        ]);

        $items = Item::query()->withPivotMarkers('items')->get();

        static::assertTrue($items->first()->items->first()->pivot->relationLoaded('markers'));
        static::assertCount(1, $items->first()->items->first()->pivot->markers);
    }

    /**
     * A model can have grouped relationship markers
     *
     * @test
     */
    public function a_model_can_have_grouped_relationship_markers(): void
    {
        $group = MarkerGroup::query()->forceCreate(['name' => 'Group']);
        $markerInGroup = Marker::query()->forceCreate(['name' => 'Marker', 'marker_group_id' => $group->id]);
        $markerNotInGroup = Marker::query()->forceCreate(['name' => 'Marker']);

        /** @var \App\Item $item */
        $item = Item::query()->forceCreate([]);
        $relatedItem = Item::query()->forceCreate([]);

        $item->items()->attach($relatedItem, [
            'markers' => [$markerInGroup->id, $markerNotInGroup->id],
        ]);

        static::assertCount(2, $relatedItem->reverseItems->first()->pivot->markers);
        static::assertCount(1, $relatedItem->reverseItems->first()->pivot->markersFromGroup($group)->getResults());
    }

    /**
     * A model can eager load grouped relationship markers
     *
     * @test
     */
    public function a_model_can_eager_load_grouped_relationship_markers(): void
    {
        $group = MarkerGroup::query()->forceCreate(['name' => 'Group']);
        $markerInGroup = Marker::query()->forceCreate(['name' => 'Marker', 'marker_group_id' => $group->id]);
        $markerNotInGroup = Marker::query()->forceCreate(['name' => 'Marker']);

        /** @var \App\Item $item */
        $item = Item::query()->forceCreate([]);
        $relatedItem = Item::query()->forceCreate([]);

        $item->items()->attach($relatedItem, [
            'markers' => [$markerInGroup->id, $markerNotInGroup->id],
        ]);

        $item->loadWithMarkersFromGroup($group, 'items');

        $relationName = 'markersFromGroup|'.$group->id;

        static::assertTrue($item->items->first()->pivot->relationLoaded($relationName));
        static::assertCount(1, $item->items->first()->pivot->getMarkersFromGroup($group));
    }

    /**
     * A model can eager load grouped relationship markers on the query
     *
     * @test
     */
    public function a_model_can_eager_load_grouped_relationship_markers_on_the_query(): void
    {
        $group = MarkerGroup::query()->forceCreate(['name' => 'Group']);
        $markerInGroup = Marker::query()->forceCreate(['name' => 'Marker', 'marker_group_id' => $group->id]);
        $markerNotInGroup = Marker::query()->forceCreate(['name' => 'Marker']);

        /** @var \App\Item $item */
        $item = Item::query()->forceCreate([]);
        $relatedItem = Item::query()->forceCreate([]);

        $item->items()->attach($relatedItem, [
            'markers' => [$markerInGroup->id, $markerNotInGroup->id],
        ]);

        $items = $item->items()->withPivotMarkersFromGroup($group)->get();

        $relationName = 'markersFromGroup|'.$group->id;

        static::assertTrue($items->first()->pivot->relationLoaded($relationName));
        static::assertCount(1, $items->first()->pivot->getMarkersFromGroup($group));
    }

    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('items', static function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });

        Schema::create('itemables', static function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('related_id');
            $table->unsignedInteger('foreign_id');
        });
    }
}

class Item extends Model implements MarkableModel
{
    use HasAllMarkers;

    public function items(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(
            __CLASS__,
            'itemables',
            'foreign_id',
            'related_id'
        )->using(MarkablePivot::class)->withPivot('id');
    }

    public function reverseItems(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(
            __CLASS__,
            'itemables',
            'related_id',
            'foreign_id'
        )->using(MarkablePivot::class)->withPivot('id');
    }
}
