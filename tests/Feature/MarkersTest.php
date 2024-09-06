<?php

declare(strict_types=1);

use App\Models\Page;
use App\Models\Marker;
use App\Models\Mapping;
use App\Models\MarkerGroup;
use Markers\Core\MarkerType;
use App\Core\Mappings\MarkerFilterOperator;
use App\Core\Mappings\Features\MappingFeatureType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\LighthouseHelpers\InteractsWithGraphQLExceptionHandling;

uses(InteractsWithGraphQLExceptionHandling::class);
uses(RefreshDatabase::class);

test('marker groups can be fetched from the api', function () {
    $user = createUser();
    /** @var \App\Models\Marker $marker */
    $marker = Marker::factory()->create();
    $group = $marker->group;

    $this->be($user)->assertGraphQL(
        ["markerGroup(id: \"$group->global_id\")" => [
            'id' => $group->global_id,
            'name' => $group->name,
            'description' => $group->description,
            'markerCount' => 1,
            'markers' => [[
                'id' => $marker->global_id,
                'name' => $marker->name,
                'order' => (int) $marker->fresh()->order,
            ]],
        ]]
    );
});

test('a marker group can be created with markers', function () {
    $user = createUser();

    $this->be($user)->assertGraphQLMutation(
        'createMarkerGroup(input: $input)',
        ['input: CreateMarkerGroupInput!' => [
            'name' => 'Status',
            'type' => 'STATUS',
            'markers' => [
                ['name' => 'Pending'],
                ['name' => 'Completed'],
                ['name' => 'Overdue'],
            ],
        ]]
    )->assertSuccessfulGraphQL();
    $group = $user->firstPersonalBase()->markerGroups->first();
    expect($group->name)->toBe('Status');
    expect($group->markers)->toHaveCount(3);
    expect($group->markers->first()->name)->toBe('Pending');
});

test('markers can be added to a group', function () {
    $user = createUser();
    /** @var \Markers\Models\MarkerGroup $group */
    $group = MarkerGroup::factory()->create();

    $this->be($user)->assertGraphQLMutation(
        'createMarker(input: $input)',
        ['input: CreateMarkerInput!' => [
            'groupId' => $group->global_id,
            'name' => 'Larry',
        ]]
    );
    expect($group->markers->first()->name)->toBe('Larry');
});

test('markers can be edited on a group', function () {
    $user = createUser();
    /** @var \Markers\Models\Marker $marker */
    $marker = Marker::factory()->create(['name' => 'Larry']);
    $group = $marker->group;

    $this->be($user)->assertGraphQLMutation(
        'updateMarker(input: $input)',
        ['input: UpdateMarkerInput!' => [
            'groupId' => $group->global_id,
            'id' => $marker->global_id,
            'name' => 'Toby',
        ]]
    );
    expect($group->markers->first()->name)->toBe('Toby');
});

test('a marker can be removed from a group', function () {
    $user = createUser();
    /** @var \Markers\Models\Marker $marker */
    $marker = Marker::factory()->create(['name' => 'Larry']);
    $group = $marker->group;

    $this->be($user)->assertGraphQLMutation(
        'deleteMarker(input: $input)',
        ['input: DeleteMarkerInput!' => [
            'groupId' => $group->global_id,
            'id' => $marker->global_id,
        ]]
    );
    expect($group->markers)->toBeEmpty();
});

test('markers can be reordered', function () {
    $user = createUser();
    /** @var \Markers\Models\MarkerGroup $group */
    $group = MarkerGroup::factory()->create();
    [$firstMarker, $secondMarker, $thirdMarker] = $group->markers()->saveMany(Marker::factory(3)->make())->all();

    $this->be($user)->graphQL("
    mutation {
        moveSecondMarkerToStart: moveMarker(input: {
            groupId: \"$group->global_id\",
            id: \"$secondMarker->global_id\",
        }) { code },
        moveThirdMarkerAfterSecond: moveMarker(input: {
            groupId: \"$group->global_id\",
            id: \"$thirdMarker->global_id\",
            previousId: \"$secondMarker->global_id\",
        }) { code }
    }
    ")->assertSuccessfulGraphQL();
    static::assertSame(
        [$secondMarker->id, $thirdMarker->id, $firstMarker->id],
        $group->markers->pluck('id')->all()
    );
});

test('a user can see a single marker group', function () {
    $user = createUser();

    $group = createMarkerGroup($user);

    $id = $group->globalId();

    $this->be($user)->graphQL("
    {
        markerGroup(id: \"$id\") { id }
    }
    ")->assertJson([
        'data' => [
            'markerGroup' => ['id' => $id],
        ],
    ], true);
});

test('a user can see their marker groups', function () {
    $user = createUser();

    $firstMarkerGroup = createMarkerGroup($user);

    $secondMarkerGroup = createMarkerGroup($user);

    $this->be($user)->assertGraphQL([
        'markerGroups' => [
            'edges' => [
                ['node' => [
                    'id' => $secondMarkerGroup->globalId(),
                ]],
                ['node' => [
                    'id' => $firstMarkerGroup->globalId(),
                ]],
            ],
        ],
    ]);
});

test('a user can filter marker groups by used features', function () {
    $user = createUser();
    $space = $user->firstSpace();
    $todosMarkerGroup = MarkerGroup::factory()->create();
    $space->enableMarkerFeatures($todosMarkerGroup, [MappingFeatureType::TODOS]);
    $calendarMarkerGroup = MarkerGroup::factory()->create();
    $space->enableMarkerFeatures($calendarMarkerGroup, [MappingFeatureType::EVENTS]);
    $this->be($user)->assertGraphQL([
        'markerGroups' => ['edges' => [
            ['node' => ['id' => $calendarMarkerGroup->global_id]],
            ['node' => ['id' => $todosMarkerGroup->global_id]],
        ]],
        "todoGroups: markerGroups(usedByFeatures: [TODOS], spaceIds: [\"$space->global_id\"])" => ['edges' => [
            ['node' => ['id' => $todosMarkerGroup->global_id]],
        ]],
        "calendarGroups: markerGroups(usedByFeatures: [EVENTS], spaceIds: [\"$space->global_id\"])" => ['edges' => [
            ['node' => ['id' => $calendarMarkerGroup->global_id]],
        ]],
    ]);
});

test('a user can filter marker groups by used mappings', function () {
    $user = createUser();
    $mappingMarkerGroup = createMarkerGroup($user);
    $otherMarkerGroup = createMarkerGroup($user);
    $mapping = Mapping::factory()->create(['space_id' => $user->firstSpace()]);
    $mapping->addMarkerGroup($mappingMarkerGroup);
    $this->be($user)->assertGraphQL([
        'markerGroups' => ['edges' => [
            ['node' => ['id' => $otherMarkerGroup->global_id]],
            ['node' => ['id' => $mappingMarkerGroup->global_id]],
        ]],
        "mappingGroups: markerGroups(usedByMappings: [\"$mapping->global_id\"])" => ['edges' => [
            ['node' => ['id' => $mappingMarkerGroup->global_id]],
        ]],
    ]);
});

test('a user can assign their marker groups to mappings and features', function () {
    $user = createUser();
    $space = $user->firstSpace();
    $mapping = Mapping::factory()->create(['space_id' => $space]);
    $this->be($user)->assertGraphQLMutation(
        ['createMarkerGroup(input: $input)' => [
            'code' => '200',
            'markerGroup' => [
                'usedByFeatures' => [[
                    'space' => ['id' => $space->global_id],
                    'features' => ['TODOS'],
                ]],
                'usedByMappings' => [['id' => $mapping->global_id]],
            ],
        ]],
        ['input: CreateMarkerGroupInput!' => [
            'name' => 'Status',
            'type' => 'STATUS',
            'usedByFeatures' => [[
                'spaceId' => $space->global_id,
                'features' => ['TODOS'],
            ]],
            'usedByMappings' => [$mapping->global_id],
        ]],
    );

    $markerGroup = $user->firstPersonalBase()->markerGroups()->first();
    expect($space->settings->markerGroups[$markerGroup->id])->toBe([MappingFeatureType::TODOS]);
});

test('a user can remove mappings and features from marker groups', function () {
    $this->withoutGraphQLExceptionHandling();
    $user = createUser();
    /** @var \App\Models\Mapping $mapping */
    $mapping = Mapping::factory()->create(['space_id' => $user->firstSpace()]);
    $markerGroup = $user->firstPersonalBase()->markerGroups()
        ->create([
            'name' => 'Status',
            'type' => MarkerType::STATUS,
            'features' => ['TODOS'],
        ]);
    $mapping->addMarkerGroup($markerGroup);
    $this->be($user)->assertGraphQLMutation(
        'updateMarkerGroup(input: $input)',
        ['input: UpdateMarkerGroupInput!' => [
            'id' => $markerGroup->global_id,
            'usedByFeatures' => [],
            'usedByMappings' => [],
        ]],
    );

    expect($user->firstPersonalBase()->markerGroups->first()->features)->toBeEmpty();
});

test('a user cannot remove marker groups from a mapping that uses it to filter pages', function () {
    $this->withGraphQLExceptionHandling();
    $user = createUser();
    /** @var \App\Models\Mapping $mapping */
    $mapping = Mapping::factory()->create(['space_id' => $user->firstSpace()]);
    $markerGroup = createMarkerGroup($user, [], 1);
    $mapping->addMarkerGroup($markerGroup);
    createEntitiesPage($mapping, ['markerFilters' => [['markerId' => $markerGroup->markers->first()->global_id, 'operator' => MarkerFilterOperator::IS]]]);
    $this->be($user)->assertFailedGraphQLMutation(
        'updateMarkerGroup(input: $input)',
        ['input: UpdateMarkerGroupInput!' => [
            'id' => $markerGroup->global_id,
            'usedByMappings' => [],
        ]],
    );

    expect($user->firstPersonalBase()->markerGroups->first()->features)->toBeEmpty();
});

test('a user can add a marker group to a node', function () {
    $user = createUser();
    $marker = Marker::factory()->create();
    $mapping = Mapping::factory()->create([
        'space_id' => $user->firstSpace(),
        'name' => 'People',
        'markerGroups' => [['group' => $marker->group]],
    ]);

    $item = createItem($mapping);

    $this->be($user)->assertGraphQLMutation(
        'setMarker(input: $input)',
        ['input: SetMarkerInput!' => [
            'markerId' => $marker->global_id,
            'markableId' => $item->global_id,
        ]],
    );

    $user->firstPersonalBase()->run(fn () => expect($item->markers)->toHaveCount(1));
});

test('a user can remove a marker from a node', function () {
    $user = createUser();
    $marker = Marker::factory()->create();
    $mapping = Mapping::factory()->create(
        [
            'space_id' => $user->firstSpace(),
            'name' => 'People',
            'markerGroups' => [['group' => $marker->group]],
        ],
    );

    /** @var \App\Models\Item $item */
    $item = createItem($mapping);

    $item->markers()->attach($marker);

    $this->be($user)->assertGraphQLMutation(
        'removeMarker(input: $input)',
        ['input: RemoveMarkerInput!' => [
            'markerId' => $marker->global_id,
            'markableId' => $item->global_id,
        ]],
    );

    $user->firstPersonalBase()->run(fn () => expect($item->markers)->toBeEmpty());
});

test('a user cannot create a marker group if it exceeds account limits', function () {
    $this->withGraphQLExceptionHandling();
    $user = createUser();
    createMarkerGroup($user, ['type' => MarkerType::TAG]);
    createMarkerGroup($user, ['type' => MarkerType::TAG]);
    createMarkerGroup($user, ['type' => MarkerType::TAG]);
    createMarkerGroup($user, ['type' => MarkerType::TAG]);

    $this->be($user)->assertFailedGraphQLMutation(
        'createMarkerGroup(input: $input)',
        ['input: CreateMarkerGroupInput!' => [
            'name' => 'Test',
            'type' => 'TAG',
        ]],
    )->assertGraphQLValidationError('limit', 'You have reached the limit for this account.');
});

test('setting a status marker does not remove other markers', function () {
    $user = createUser();
    $marker = Marker::factory()->create();
    $status = Marker::factory()->create(['marker_group_id' => MarkerGroup::factory()->create(['type' => MarkerType::STATUS])]);
    $mapping = Mapping::factory()->create([
        'space_id' => $user->firstSpace(),
        'name' => 'People',
        'markerGroups' => [
            ['group' => $marker->group],
            ['group' => $status->group],
        ],
    ]);

    $item = createItem($mapping);

    $item->markers()->attach($marker);

    $this->be($user)->assertGraphQLMutation(
        'setMarker(input: $input)',
        ['input: SetMarkerInput!' => [
            'markerId' => $status->global_id,
            'markableId' => $item->global_id,
        ]],
    );

    $user->firstPersonalBase()->run(function () use ($item, $status, $marker) {
        expect($item->markersFromGroup($status->group)->getResults())->toHaveCount(1);
        expect($item->markersFromGroup($marker->group)->getResults())->toHaveCount(1);
    });
});

test('a user can delete a marker group', function () {
    $user = createUser();
    $markerGroup = createMarkerGroup($user);

    $this->be($user)->assertGraphQLMutation(
        'deleteMarkerGroup(input: $input).code',
        ['input: DeleteMarkerGroupInput!' => ['id' => $markerGroup->global_id]],
    );
});

test('deleting a marker group removes it from all mappings and page designs', function () {
    $user = createUser();
    $markerGroup = createMarkerGroup($user, [], 1);
    $marker = $markerGroup->markers->first();
    $mapping = Mapping::factory()->create(['space_id' => $user->firstSpace()]);
    $mapping->addMarkerGroup($markerGroup);
    $mapping->pages()->create([
        'name' => 'Test page',
        'space_id' => $user->firstSpace()->id,
        'type' => 'ENTITIES',
        'design' => [
            'defaultView' => 'TILE',
            'views' => [[
                'name' => 'Line',
                'id' => 'LINE',
                'viewType' => 'LINE',
                'template' => 'Line1',
                'visibleData' => [
                    [
                        'dataType' => 'FIELDS',
                        'slot' => 'HEADER1',
                        'combo' => 4,
                        'formattedId' => 'SYSTEM_NAME',
                    ],
                    [
                        'dataType' => 'MARKERS',
                        'slot' => 'REG1',
                        'formattedId' => $marker->global_id,
                    ],
                    [
                        'dataType' => 'FIELDS',
                        'slot' => 'REG2',
                        'formattedId' => 'DESCRIPTION',
                    ],
                ],
            ]],
        ],
    ]);

    $this->be($user)->assertGraphQLMutation(
        'deleteMarkerGroup(input: $input).code',
        ['input: DeleteMarkerGroupInput!' => ['id' => $markerGroup->global_id]],
    );

    $mapping->refresh();
    expect($mapping->markerGroups)->toBeEmpty();
    expect($mapping->pages->first()->design)
        ->not->toContain($marker->global_id);
});

test('a user cannot delete a marker group that is used as a page filter', function () {
    $this->withGraphQLExceptionHandling();
    $user = createUser();
    $markerGroup = createMarkerGroup($user, [], 1);
    $mapping = createMapping($user);
    $page = Page::factory()->create([
        'space_id' => $user->firstSpace(),
        'mapping_id' => $mapping,
        'name' => 'Test page',
        'markerFilters' => [['markerId' => $markerGroup->markers->first()->global_id, 'operator' => MarkerFilterOperator::IS]],
    ]);

    $this->be($user)->assertFailedGraphQLMutation(
        'deleteMarkerGroup(input: $input).code',
        ['input: DeleteMarkerGroupInput!' => ['id' => $markerGroup->global_id]],
    )->assertGraphQLValidationError('input.id', 'This marker group is used to filter pages. Please remove it from the pages first. Page(s): "Test page"');
});

test('a user cannot delete a marker that is used as a page filter', function () {
    $this->withGraphQLExceptionHandling();
    $user = createUser();
    $markerGroup = createMarkerGroup($user, [], 1);
    $marker = $markerGroup->markers->first();
    $mapping = createMapping($user);
    $page = Page::factory()->create([
        'space_id' => $user->firstSpace(),
        'mapping_id' => $mapping,
        'name' => 'Test page',
        'markerFilters' => [['markerId' => $marker->global_id, 'operator' => MarkerFilterOperator::IS]],
    ]);

    $this->be($user)->assertFailedGraphQLMutation(
        'deleteMarker(input: $input).code',
        ['input: DeleteMarkerInput!' => [
            'id' => $marker->global_id,
            'groupId' => $markerGroup->global_id,
        ]],
    )->assertGraphQLValidationError('input.id', 'This marker is used to filter pages. Please remove it from the pages first. Page(s): "Test page"');
});
