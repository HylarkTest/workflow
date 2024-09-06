<?php

declare(strict_types=1);

use App\Models\MarkerGroup;
use Markers\Core\MarkerType;
use Tests\Mappings\Concerns\ChangesSchema;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;

uses(ChangesSchema::class);
uses(MakesGraphQLRequests::class);
uses(RefreshDatabase::class);

test('a marker can be added to an item', function () {
    $user = createUser();
    /** @var \Markers\Models\MarkerGroup $markerGroup */
    $markerGroup = MarkerGroup::query()->create(['name' => 'markers', 'type' => MarkerType::TAG]);
    /** @var \Markers\Models\Marker $marker */
    $marker = $markerGroup->markers()->create(['name' => 'Thing', 'color' => '#FFF']);
    $mapping = createMapping($user, [
        'name' => 'Items',
        'singular_name' => 'Item',
    ]);
    $mapping->addMarkerGroup([
        'id' => 'markers',
        'name' => 'Markers',
        'group' => $markerGroup,
    ]);

    $item = createItem($mapping);

    $this->be($user)->graphQL('
        mutation SetMarker($input: SetMarkerInput!) {
            setMarker(input: $input) {
                code
            }
        }
    ', ['input' => [
        'markableId' => $item->global_id,
        'markerId' => $marker->global_id,
        'context' => 'markers',
    ]]);

    $this->assertGraphQl(['items' => ["item(id: \"{$item->global_id}\")" => [
        'markers' => ['markers' => [
            ['id' => $marker->global_id],
        ]],
    ]]]);
});

test('a marker cannot be added to an item from a different marker group', function () {
    $user = createUser();
    $this->withGraphQLExceptionHandling();
    /** @var \Markers\Models\MarkerGroup $markerGroup */
    $markerGroup = MarkerGroup::query()->create(['name' => 'markers', 'type' => MarkerType::TAG]);
    /** @var \Markers\Models\MarkerGroup $wrongGroup */
    $wrongGroup = MarkerGroup::query()->create(['name' => 'other markers', 'type' => MarkerType::TAG]);
    /** @var \Markers\Models\Marker $marker */
    $marker = $wrongGroup->markers()->create(['name' => 'Thing', 'color' => '#FFF']);
    $mapping = createMapping($user, [
        'name' => 'Items',
        'singular_name' => 'Item',
    ]);
    $mapping->addMarkerGroup([
        'id' => 'markers',
        'name' => 'Markers',
        'group' => $markerGroup,
    ]);

    $item = createItem($mapping);

    $this->be($user)->graphQl('
        mutation SetMarker($input: SetMarkerInput!) {
            setMarker(input: $input) {
                code
            }
        }
    ', ['input' => [
        'markableId' => $item->global_id,
        'markerId' => $marker->global_id,
        'context' => 'markers',
    ]])->assertJson(['errors' => [['extensions' => ['validation' => [
        'input.markableId' => ['The markable ID cannot have the specified tag'],
    ]]]]]);
});

test('a marker can be added on a relationship', function () {
    static::markTestSkipped('Wait until markers can be added to relationships');
    $user = createUser();
    /** @var \Markers\Models\MarkerGroup $markerGroup */
    $markerGroup = MarkerGroup::query()->create(['name' => 'markers', 'type' => MarkerType::TAG]);
    /** @var \Markers\Models\Marker $marker */
    $marker = $markerGroup->markers()->create(['name' => 'Thing', 'color' => '#FFF']);
    $toMapping = createMapping($user, ['name' => 'Children', 'singularName' => 'Child']);
    $fromMapping = createMapping($user, [
        'name' => 'Parents',
        'singularName' => 'Parent',
        'relationships' => [
            [
                'id' => 'children',
                'name' => 'Children',
                'type' => 'ONE_TO_MANY',
                'to' => $toMapping->getKey(),
            ],
        ],
    ]);

    $fromMapping->addMarkerGroup([
        'id' => 'markers',
        'name' => 'Markers',
        'relationship' => 'children',
        'group' => $markerGroup,
    ]);

    /** @var \Mappings\Models\Item $parent */
    $parent = $fromMapping->items()->create(['data' => ['name' => 'Parent1']]);
    /** @var \Mappings\Models\Item $child */
    $child = $toMapping->items()->create(['data' => ['name' => 'Child1']]);

    $childrenRelation = $parent->relatedItems($fromMapping->relationships->first());
    $childrenRelation->attach($child);

    $this->be($user)->graphQL(/* Set Marker query */);
    $this->graphQL("
        query {
            items {
                parent(id: \"{$parent->global_id}\") {
                    relations {
                        children(first: 1) {
                            edges {
                                node { id }
                                markers {
                                    markers { id }
                                }
                            }
                        }
                    }
                }
            }
        }
    ")->assertJson(['data' => ['items' => ['parent' => ['relations' => ['children' => ['edges' => [
        [
            'node' => ['id' => $child->globalId()],
            'markers' => [
                'markers' => [['id' => $marker->globalId()]],
            ],
        ],
    ]]]]]]]);
});

test('a marker can be removed from a relationship', function () {
    static::markTestSkipped('Wait until markers can be added to relationships');
    $user = createUser();
    /** @var \Markers\Models\MarkerGroup $markerGroup */
    $markerGroup = MarkerGroup::query()->create(['name' => 'markers', 'type' => MarkerType::TAG]);
    /** @var \Markers\Models\Marker $marker */
    $marker = $markerGroup->markers()->create(['name' => 'Thing', 'color' => '#FFF']);
    /** @var \Markers\Models\Marker $otherMarker */
    $otherMarker = $markerGroup->markers()->create(['name' => 'Other thing', 'color' => '#FFF']);
    $toMapping = createMapping($user, ['name' => 'Children', 'singularName' => 'Child']);
    $fromMapping = createMapping($user, [
        'name' => 'Parents',
        'singularName' => 'Parent',
        'relationships' => [
            [
                'id' => 'children',
                'name' => 'Children',
                'type' => 'ONE_TO_MANY',
                'to' => $toMapping->getKey(),
            ],
        ],
    ]);

    $fromMapping->addMarkerGroup([
        'id' => 'markers',
        'name' => 'Markers',
        'relationship' => 'children',
        'group' => $markerGroup,
    ]);

    /** @var \Mappings\Models\Item $parent */
    $parent = $fromMapping->items()->create(['data' => ['name' => 'Parent1']]);
    /** @var \Mappings\Models\Item $child */
    $child = $toMapping->items()->create(['data' => ['name' => 'Child1']]);

    $childrenRelation = $parent->relatedItems($fromMapping->relationships->first());
    $childrenRelation->attach($child, [
        'markers' => [$marker->id, $otherMarker->id],
    ]);

    $this->be($user)->graphQL(/* Set marker query */);
    $this->graphQL("
        query {
            items {
                parent(id: \"{$parent->global_id}\") {
                    relations {
                        children(first: 1) {
                            edges {
                                node { id }
                                markers {
                                    markers { id }
                                }
                            }
                        }
                    }
                }
            }
        }
    ")->assertJsonCount(1, 'data.items.parent.relations.children.edges.0.markers.markers');
});

test('a status marker can be set on a relationship', function () {
    static::markTestSkipped('Wait until markers can be added to relationships');
    $user = createUser();
    /** @var \Markers\Models\MarkerGroup $markerGroup */
    $markerGroup = MarkerGroup::query()->create(['name' => 'markers', 'type' => MarkerType::STATUS]);
    /** @var \Markers\Models\Marker $marker */
    $marker = $markerGroup->markers()->create(['name' => 'Thing', 'color' => '#FFF']);
    $toMapping = createMapping($user, ['name' => 'Children', 'singularName' => 'Child']);
    $fromMapping = createMapping($user, [
        'name' => 'Parents',
        'singularName' => 'Parent',
        'relationships' => [
            [
                'id' => 'children',
                'name' => 'Children',
                'type' => 'ONE_TO_MANY',
                'to' => $toMapping->getKey(),
            ],
        ],
    ]);

    $fromMapping->addMarkerGroup([
        'id' => 'status',
        'name' => 'Status',
        'relationship' => 'children',
        'group' => $markerGroup,
    ]);

    /** @var \Mappings\Models\Item $parent */
    $parent = $fromMapping->items()->create(['data' => ['name' => 'Parent1']]);
    /** @var \Mappings\Models\Item $child */
    $child = $toMapping->items()->create(['data' => ['name' => 'Child1']]);

    $childrenRelation = $parent->relatedItems($fromMapping->relationships->first());
    $childrenRelation->attach($child);

    $this->be($user)->graphQL(/* Set marker query */);
    $this->graphQL("
        query {
            items {
                parent(id: \"{$parent->global_id}\") {
                    relations {
                        children(first: 1) {
                            edges {
                                node { id }
                                markers {
                                    status { id }
                                }
                            }
                        }
                    }
                }
            }
        }
    ")->assertJson(['data' => ['items' => ['parent' => ['relations' => ['children' => ['edges' => [
        [
            'node' => ['id' => $child->globalId()],
            'markers' => [
                'status' => ['id' => $marker->globalId()],
            ],
        ],
    ]]]]]]]);
});

test('a status marker can be removed from a relationship', function () {
    static::markTestSkipped('Wait until markers can be added to relationships');
    $user = createUser();
    /** @var \Markers\Models\MarkerGroup $markerGroup */
    $markerGroup = MarkerGroup::query()->create(['name' => 'markers', 'type' => MarkerType::STATUS]);
    /** @var \Markers\Models\Marker $marker */
    $marker = $markerGroup->markers()->create(['name' => 'Thing', 'color' => '#FFF']);
    $toMapping = createMapping($user, ['name' => 'Children', 'singularName' => 'Child']);
    $fromMapping = createMapping($user, [
        'name' => 'Parents',
        'singularName' => 'Parent',
        'relationships' => [
            [
                'id' => 'children',
                'name' => 'Children',
                'type' => 'ONE_TO_MANY',
                'to' => $toMapping->getKey(),
            ],
        ],
    ]);

    $fromMapping->addMarkerGroup([[
        'id' => 'status',
        'name' => 'Status',
        'relationship' => 'children',
        'group' => $markerGroup,
    ]]);

    /** @var \Mappings\Models\Item $parent */
    $parent = $fromMapping->items()->create(['data' => ['name' => 'Parent1']]);
    /** @var \Mappings\Models\Item $child */
    $child = $toMapping->items()->create(['data' => ['name' => 'Child1']]);

    $childrenRelation = $parent->relatedItems($fromMapping->relationships->first());
    $childrenRelation->attach($child, [
        'markers' => $marker,
    ]);

    $this->be($user)->graphQL(/* Set marker query */);
    $this->postJson("
        query {
            items {
                parent(id: \"{$parent->global_id}\") {
                    relations {
                        children(first: 1) {
                            edges {
                                node { id }
                                markers {
                                    status { id }
                                }
                            }
                        }
                    }
                }
            }
        }
    ")->assertJson(['data' => ['items' => ['parent' => ['relations' => ['children' => ['edges' => [
        [
            'node' => ['id' => $child->globalId()],
            'markers' => [
                'status' => null,
            ],
        ],
    ]]]]]]]);
});

test('a status marker can be set on an item', function () {
    $user = createUser();
    /** @var \Markers\Models\MarkerGroup $markerGroup */
    $markerGroup = MarkerGroup::query()->create(['name' => 'markers', 'type' => MarkerType::STATUS]);
    /** @var \Markers\Models\Marker $marker */
    $marker = $markerGroup->markers()->create(['name' => 'Thing', 'color' => '#FFF']);
    $secondMarker = $markerGroup->markers()->create(['name' => 'Other thing', 'color' => '#FFF']);
    $mapping = createMapping($user, [
        'name' => 'Items',
        'singular_name' => 'Item',
    ]);

    $mapping->addMarkerGroup([
        'id' => 'markers',
        'name' => 'Status',
        'group' => $markerGroup,
    ]);

    $item = createItem($mapping);
    $secondItem = createItem($mapping);

    $this->be($user)->graphQL('
        mutation SetMarker($first: SetMarkerInput!, $second: SetMarkerInput!) {
            first: setMarker(input: $first) {
                code
            }
            second: setMarker(input: $second) {
                code
            }
        }
    ', [
        'first' => ['markableId' => $item->global_id, 'markerId' => $marker->global_id, 'context' => 'markers'],
        'second' => ['markableId' => $secondItem->global_id, 'markerId' => $secondMarker->global_id, 'context' => 'markers'],
    ]);

    $this->graphQL('
        {
            items {
                items {
                    edges {
                        node {
                            markers {
                                status { id }
                            }
                        }
                    }
                }
            }
        }
    ')->assertJson(['data' => ['items' => ['items' => ['edges' => [
        ['node' => ['markers' => ['status' => ['id' => $secondMarker->global_id]]]],
        ['node' => ['markers' => ['status' => ['id' => $marker->global_id]]]],
    ]]]]]);
});

test('a marker can be removed from an item', function () {
    $user = createUser();
    /** @var \Markers\Models\MarkerGroup $markerGroup */
    $markerGroup = MarkerGroup::query()->create(['name' => 'markers', 'type' => MarkerType::TAG]);
    /** @var \Markers\Models\Marker $marker */
    $marker = $markerGroup->markers()->create(['name' => 'Thing', 'color' => '#FFF']);
    $mapping = createMapping($user, [
        'name' => 'Items',
        'singular_name' => 'Item',
    ]);

    $mapping->addMarkerGroup([
        'id' => 'markers',
        'name' => 'Markers',
        'group' => $markerGroup,
    ]);

    $item = createItem($mapping);

    $item->markers()->attach($marker, ['context' => 'markers']);

    $this->be($user)->graphQL('
        mutation RemoveMarker($input: RemoveMarkerInput!) {
            removeMarker(input: $input) {
                code
            }
        }
    ', ['input' => [
        'markableId' => $item->global_id,
        'markerId' => $marker->global_id,
        'context' => 'markers',
    ]]);

    $user->firstPersonalBase()->run(fn () => expect($item->markers)->toBeEmpty());
});

test('a status marker can be removed from an item', function () {
    $user = createUser();
    /** @var \Markers\Models\MarkerGroup $markerGroup */
    $markerGroup = MarkerGroup::query()->create(['name' => 'markers', 'type' => MarkerType::STATUS]);
    /** @var \Markers\Models\Marker $marker */
    $marker = $markerGroup->markers()->create(['name' => 'Thing', 'color' => '#FFF']);
    $mapping = createMapping($user, [
        'name' => 'Items',
        'singular_name' => 'Item',
    ]);

    $mapping->addMarkerGroup([
        'id' => 'markers',
        'name' => 'Status',
        'group' => $markerGroup,
    ]);

    $item = createItem($mapping);

    $item->markers()->attach($marker, ['context' => 'markers']);

    $this->be($user)->graphQL('
        mutation RemoveMarker($input: RemoveMarkerInput!) {
            removeMarker(input: $input) { code }
        }
    ', ['input' => [
        'markableId' => $item->global_id,
        'markerId' => $marker->global_id,
        'context' => 'markers',
    ]])->assertSuccessful();

    $user->firstPersonalBase()->run(fn () => expect($item->markers)->toBeEmpty());
});

test('adding and removing a marker to an item creates an action', function () {
    config([
        'actions.automatic' => true,
        'actions.mandatory_performer' => false,
    ]);
    $user = createUser();
    /** @var \Markers\Models\MarkerGroup $markerGroup */
    $markerGroup = MarkerGroup::query()->create(['name' => 'markers', 'type' => MarkerType::TAG]);
    /** @var \Markers\Models\Marker $marker */
    $marker = $markerGroup->markers()->create(['name' => 'Thing', 'color' => '#FFF']);
    $mapping = createMapping($user, [
        'name' => 'Items',
        'singular_name' => 'Item',
    ]);
    $mapping->addMarkerGroup([
        'id' => 'markers',
        'name' => 'Markers',
        'group' => $markerGroup,
    ]);

    $item = createItem($mapping);

    $this->be($user)->assertGraphQLMutation(
        'setMarker(input: $input).code',
        ['input: SetMarkerInput!' => [
            'markableId' => $item->global_id,
            'markerId' => $marker->global_id,
            'context' => 'markers',
        ]]
    );

    $this->be($user)->assertGraphQLMutation(
        'removeMarker(input: $input).code',
        ['input: RemoveMarkerInput!' => [
            'markableId' => $item->global_id,
            'markerId' => $marker->global_id,
            'context' => 'markers',
        ]]
    );

    $actions = $item->actions;

    expect($actions)->toHaveCount(3);
    $addAction = $actions->get(1);
    $removeAction = $actions->first();

    expect($addAction->description(false))->toBe('Added marker "Thing" to "Item".')
        ->and($removeAction->description(false))->toBe('Removed marker "Thing" from "Item".')
        ->and($addAction->changes())->toBeNull()
        ->and($removeAction->changes())->toBeNull();
});
