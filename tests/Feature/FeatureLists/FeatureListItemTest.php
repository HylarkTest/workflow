<?php

declare(strict_types=1);

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Markers\Core\MarkerType;
use Illuminate\Http\UploadedFile;
use App\Core\Mappings\Features\MappingFeatureType;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

$listItemTypes = [
    ['drive', 'document'],
    ['calendar', 'event'],
    ['linkList', 'link'],
    ['notebook', 'note'],
    ['pinboard', 'pin'],
    ['todoList', 'todo'],
];

$listItemTypesWithParams = fn () => [
    ['drive', 'document', ['filename' => 'Test', 'file' => UploadedFile::fake()->create('Test.txt')]],
    ['calendar', 'event', [
        'name' => 'Test',
        'timezone' => 'Europe/London',
        'startAt' => now(),
        'endAt' => now()->addHour(),
        'isAllDay' => false,
        'recurrence' => ['frequency' => 'DAILY', 'interval' => 1, 'count' => 2],
        'description' => 'Lorem Ipsum',
        'location' => 'London',
        'priority' => 1,
    ]],
    ['linkList', 'link', ['name' => 'Test', 'url' => 'https://hylark.com', 'description' => 'Lorem Ipsum']],
    ['notebook', 'note', ['name' => 'Test', 'plaintext' => 'Lorem Ipsum']],
    ['pinboard', 'pin', [
        'name' => 'Test',
        'description' => 'Lorem Ipsum',
        'image' => [
            'image' => UploadedFile::fake()->image('Test'),
            'url' => '',
            'xOffset' => 0,
            'yOffset' => 0,
            'width' => 0,
            'height' => 0,
            'rotate' => 0,
        ],
    ]],
    ['todoList', 'todo', [
        'name' => 'Test',
        'startAt' => now(),
        'dueBy' => now()->addHour(),
        'recurrence' => ['frequency' => 'DAILY', 'interval' => 1, 'count' => 2],
        'description' => 'Lorem Ipsum',
        'location' => 'London',
        'priority' => 1,
    ]],
];

$favoritableListItemTypes = array_filter($listItemTypes, function (array $listItem) {
    return $listItem[0] !== 'todoList' && $listItem[0] !== 'calendar';
});

$featureListMap = [
    'document' => MappingFeatureType::DOCUMENTS,
    'event' => MappingFeatureType::EVENTS,
    'link' => MappingFeatureType::LINKS,
    'note' => MappingFeatureType::NOTES,
    'pin' => MappingFeatureType::PINBOARD,
    'todo' => MappingFeatureType::TODOS,
];

test('list items can be fetched', function (string $listQuery, string $itemQuery) {
    $user = createUser();
    $list = createList($user, $listQuery, [], 1);
    $this->travel(5)->seconds();
    createListItem($list);

    $this->be($user)->assertGraphQL([
        Str::plural($itemQuery).'(orderBy: [{ field: CREATED_AT, direction: DESC }])' => [
            'edges' => [
                ['node' => ['id' => $list->children->last()->global_id]],
                ['node' => ['id' => $list->children->first()->global_id]],
            ],
        ],
    ]);
})->with($listItemTypes);

test('list items can be ordered by list', function (string $listQuery, string $itemQuery) {
    $user = createUser();
    $list1 = createList($user, $listQuery);
    $list2 = createList($user, $listQuery);
    $item1 = createListItem($list2);
    $this->travel(5)->seconds();
    $item2 = createListItem($list1);
    $this->travel(5)->seconds();
    $item3 = createListItem($list2);

    $orderType = Str::studly($itemQuery).'OrderBy';

    $this->be($user)->assertGraphQL(
        [
            Str::plural($itemQuery).'(orderBy: [$orderCreated])' => [
                'edges' => [
                    ['node' => ['id' => $item3->global_id]],
                    ['node' => ['id' => $item2->global_id]],
                    ['node' => ['id' => $item1->global_id]],
                ],
            ],
            'ordered: '.Str::plural($itemQuery).'(orderBy: [$orderList, $orderCreated])' => [
                'edges' => [
                    ['node' => ['id' => $item3->global_id]],
                    ['node' => ['id' => $item1->global_id]],
                    ['node' => ['id' => $item2->global_id]],
                ],
            ],
        ],
        [
            "orderCreated: $orderType!" => ['field' => 'CREATED_AT', 'direction' => 'DESC'],
            "orderList: $orderType!" => ['field' => mb_strtoupper(Str::snake($listQuery)), 'direction' => 'DESC'],
        ]
    );
})->with($listItemTypes);

test('list items can be ordered by name', function (string $query, string $itemQuery) {
    $nameField = $itemQuery === 'document' ? 'filename' : 'name';
    $user = createUser();
    $list = createList($user, $query, [], 5);
    $children = $list->children;
    $children[2]->update([$nameField => 'A']);
    $children[1]->update([$nameField => 'B']);
    $children[4]->update([$nameField => 'B']);
    $children[3]->update([$nameField => 'C']);
    $children[0]->update([$nameField => 'D']);

    $orderType = Str::studly($itemQuery).'OrderBy';
    $orderField = ['field' => mb_strtoupper($nameField), 'direction' => 'DESC'];

    $cursor = $this->be($user)->assertGraphQL(
        [
            'items: '.Str::plural($itemQuery).'(first: 3, orderBy: [$orderName])' => [
                'edges' => [
                    ['node' => ['id' => $children[0]->global_id]],
                    ['node' => ['id' => $children[3]->global_id]],
                    ['node' => ['id' => $children[4]->global_id]],
                ],
                'pageInfo' => ['endCursor' => new Ignore],
            ],
        ],
        ["orderName: $orderType!" => $orderField]
    )->json('data.items.pageInfo.endCursor');
    $this->be($user)
        ->assertGraphQL(
            [
                'items: '.Str::plural($itemQuery).'(first: 3, after: $cursor, orderBy: [$orderName])' => [
                    'edges' => [
                        ['node' => ['id' => $children[1]->global_id]],
                        ['node' => ['id' => $children[2]->global_id]],
                    ],
                    'pageInfo' => ['endCursor' => new Ignore],
                ],
            ],
            ['cursor: String' => $cursor, "orderName: $orderType!" => $orderField]
        );
})->with($listItemTypes);

test('list items can be filtered by list', function (string $listQuery, string $itemQuery) {
    $user = createUser();
    createList($user, $listQuery, [], 1);
    $list2 = createList($user, $listQuery, [], 1);

    $this->be($user)->assertGraphQL(
        [
            Str::plural($itemQuery)."({$listQuery}Id: \"$list2->global_id\")" => [
                'edges' => [
                    ['node' => ['id' => $list2->children()->first()->global_id]],
                ],
            ],
        ],
    );
})->with($listItemTypes);

test('list items can be paginated', function (string $listQuery, string $itemQuery) {
    $user = createUser();
    $list = createList($user, $listQuery);
    for ($i = 0; $i < 6; $i++) {
        $this->travel(5)->seconds();
        createListItem($list);
    }

    $query = Str::plural($itemQuery);
    $cursor = $this->be($user)->assertGraphQL([
        "$query(first: 3, orderBy: [{ field: CREATED_AT, direction: DESC }])" => [
            'edges' => [
                ['node' => ['id' => $list->children->get(5)->global_id]],
                ['node' => ['id' => $list->children->get(4)->global_id]],
                ['node' => ['id' => $list->children->get(3)->global_id]],
            ],
            'pageInfo' => ['endCursor' => new Ignore],
        ],
    ])->json("data.$query.pageInfo.endCursor");

    $this->be($user)->assertGraphQL([
        "$query(first: 3, after: \"$cursor\", orderBy: [{ field: CREATED_AT, direction: DESC }])" => [
            'edges' => [
                ['node' => ['id' => $list->children->get(2)->global_id]],
                ['node' => ['id' => $list->children->get(1)->global_id]],
                ['node' => ['id' => $list->children->get(0)->global_id]],
            ],
        ],
    ]);
})->with($listItemTypes);

test('list items can be fetched for a specific record', function (string $listQuery, string $itemQuery) use ($featureListMap) {
    $user = createUser();
    $mapping = createMapping($user, ['name' => 'People']);
    $mapping->enableFeature($featureListMap[$itemQuery]);
    $item = createItem($mapping);

    $list = createList($user, $listQuery, [], 2);
    $child = $list->children()->first();
    $child->items()->attach($item);

    $query = Str::plural($itemQuery);
    $this->be($user)->assertGraphQL(
        [
            "$query(forNode: \"$item->global_id\")" => [
                'edges' => [['node' => ['id' => $child->global_id]]],
            ],
            'items' => ["person(id: \"$item->global_id\")" => [
                'id' => $item->global_id,
                'features' => [$query => ['edges' => [['node' => ['id' => $child->global_id]]]]],
            ]],
        ],
    );
})->with($listItemTypes);

test('list items can be filtered by mapping', function (string $listQuery, string $itemQuery) use ($featureListMap) {
    $user = createUser();
    $mapping = createMapping($user, ['name' => 'People']);
    $mapping->enableFeature($featureListMap[$itemQuery]);
    $item = createItem($mapping);

    $list = createList($user, $listQuery, [], 2);
    $child = $list->children()->first();
    $child->items()->attach($item);

    $query = Str::plural($itemQuery);
    $this->be($user)->assertGraphQL(
        [
            "$query(forMapping: \"$mapping->global_id\")" => [
                'edges' => [['node' => ['id' => $child->global_id]]],
            ],
        ],
    );
})->with($listItemTypes);

test('list items can be favorited', function (string $listQuery, string $itemQuery) {
    $user = createUser();
    $list = createList($user, $listQuery, [], 1);
    $child = $list->children()->first();

    $type = ucfirst($itemQuery);
    $this->be($user)->assertGraphQLMutation(
        ["update$type(input: \$input)" => [
            $itemQuery => [
                'id' => $child->global_id,
                'isFavorite' => true,
            ],
        ]],
        ["input: Update{$type}Input!" => ['id' => $child->global_id, 'isFavorite' => true]]
    );
    expect($child->fresh())->isFavorite()->toBeTrue();

    $this->be($user)->assertGraphQLMutation(
        ["update$type(input: \$input)" => [
            $itemQuery => [
                'id' => $child->global_id,
                'isFavorite' => false,
            ],
        ]],
        ["input: Update{$type}Input!" => ['id' => $child->global_id, 'isFavorite' => false]]
    );

    expect($child->fresh())->isFavorite()->toBeFalse();
})->with($favoritableListItemTypes);

test('list items can be filtered by favorite', function (string $listQuery, string $itemQuery) {
    $user = createUser();
    $list = createList($user, $listQuery, [], 2);
    $child = $list->children()->first();
    $child->favorite();

    $this->be($user)->assertGraphQL(
        [
            Str::plural($itemQuery).'(filters: [{ isFavorited: true }])' => [
                'edges' => [
                    ['node' => ['id' => $child->global_id]],
                ],
            ],
        ],
    );
})->with($favoritableListItemTypes);

test('list items can be filtered by marker', function (string $listQuery, string $itemQuery) {
    $user = createUser();
    $markerGroup = createMarkerGroup($user, [], 1);
    $marker = $markerGroup->markers()->first();

    $list = createList($user, $listQuery, [], 2);
    $child = $list->children()->first();
    $child->markers()->attach($marker);

    $query = Str::plural($itemQuery);
    $this->be($user)->assertGraphQL([
        "$query(filters: [{ markers: [{ markerId: \"$marker->global_id\" }] }])" => [
            'edges' => [['node' => ['id' => $child->global_id]]],
        ],
    ]);
})->with($listItemTypes);

test('grouped list items can be filtered by marker', function (string $listQuery, string $itemQuery) {
    $user = createUser();
    $markerGroup = createMarkerGroup($user, [], 1);
    $marker = $markerGroup->markers()->first();

    $list = createList($user, $listQuery, [], 2);
    $child = $list->children()->first();
    $child->markers()->attach($marker);

    $query = ucfirst(Str::plural($itemQuery));
    $this->be($user)->assertGraphQL([
        "grouped$query(group: \"LIST\", filters: [{ markers: [{ markerId: \"$marker->global_id\" }] }])" => [
            'groups' => [[
                'edges' => [['node' => ['id' => $child->global_id]]],
            ]],
        ],
    ]);
})->with($listItemTypes);

test('list items can be grouped by marker', function (string $listQuery, string $itemQuery) {
    $user = createUser();
    $markerGroup = createMarkerGroup($user, ['type' => MarkerType::TAG], 3);
    [$marker1, $marker2, $marker3] = $markerGroup->markers;

    $list = createList($user, $listQuery, [], 10);
    $children = $list->children;
    $children[2]->markers()->attach($marker1);
    $children[3]->markers()->attach($marker1);
    $children[4]->markers()->attach($marker1);
    $children[5]->markers()->attach($marker1);
    $children[6]->markers()->attach($marker2);
    $children[7]->markers()->attach($marker2);
    $children[8]->markers()->attach($marker2);
    $children[9]->markers()->attach($marker2);

    $query = ucfirst(Str::plural($itemQuery));
    $cursors = $this->be($user)->graphQL(
        "{
            grouped$query(first: 3, group: \"marker:$markerGroup->global_id\") {
                groups {
                    groupHeader
                    group {
                        ...on Marker {
                            id
                        }
                    }
                    edges {
                        node {
                            id
                        }
                    }
                    pageInfo {
                        endCursor
                    }
                }
            }
        }",
    )->assertJson(
        ['data' => ["grouped$query" => [
            'groups' => [
                [
                    'groupHeader' => $marker1->global_id,
                    'group' => ['id' => $marker1->global_id],
                    'edges' => [
                        ['node' => ['id' => $children[5]->global_id]],
                        ['node' => ['id' => $children[4]->global_id]],
                        ['node' => ['id' => $children[3]->global_id]],
                    ],
                ],
                [
                    'groupHeader' => $marker2->global_id,
                    'group' => ['id' => $marker2->global_id],
                    'edges' => [
                        ['node' => ['id' => $children[9]->global_id]],
                        ['node' => ['id' => $children[8]->global_id]],
                        ['node' => ['id' => $children[7]->global_id]],
                    ],
                ],
                [
                    'groupHeader' => $marker3->global_id,
                    'group' => ['id' => $marker3->global_id],
                    'edges' => [],
                ],
                [
                    'groupHeader' => null,
                    'group' => null,
                    'edges' => [
                        ['node' => ['id' => $children[1]->global_id]],
                        ['node' => ['id' => $children[0]->global_id]],
                    ],
                ],
            ],
        ],
        ]])->json("data.grouped$query.groups.*.pageInfo.endCursor");

    $this->be($user)->assertGraphQL([
        "grouped$query(first: 3, group: \"marker:$markerGroup->global_id\", includeGroups: [\"$marker1->global_id\"], after: \"$cursors[0]\")" => [
            'groups' => [
                [
                    'groupHeader' => $marker1->global_id,
                    'edges' => [
                        ['node' => ['id' => $children[2]->global_id]],
                    ],
                ],
            ],
        ],
    ]);
    $this->be($user)->assertGraphQL([
        "grouped$query(first: 3, group: \"marker:$markerGroup->global_id\", includeGroups: [\"$marker2->global_id\"], after: \"$cursors[1]\")" => [
            'groups' => [
                [
                    'groupHeader' => $marker2->global_id,
                    'edges' => [
                        ['node' => ['id' => $children[6]->global_id]],
                    ],
                ],
            ],
        ],
    ]);
})->with($listItemTypes);

test('list items can be grouped by list', function (string $listQuery, string $itemQuery) {
    $user = createUser();

    $list1 = createList($user, $listQuery, [], 3);
    $list2 = createList($user, $listQuery, [], 3);
    $children = $list1->children->merge($list2->children);

    $query = ucfirst(Str::plural($itemQuery));
    $this->be($user)->assertGraphQL([
        "grouped$query(first: 3, group: \"LIST\")" => [
            'groups' => [
                [
                    'groupHeader' => $list1->global_id,
                    'edges' => [
                        ['node' => ['id' => $children[2]->global_id]],
                        ['node' => ['id' => $children[1]->global_id]],
                        ['node' => ['id' => $children[0]->global_id]],
                    ],
                ],
                [
                    'groupHeader' => $list2->global_id,
                    'edges' => [
                        ['node' => ['id' => $children[5]->global_id]],
                        ['node' => ['id' => $children[4]->global_id]],
                        ['node' => ['id' => $children[3]->global_id]],
                    ],
                ],
            ],
        ],
    ]);
})->with($listItemTypes);

test('list items can be filtered with free search', function (string $listQuery, string $itemQuery) {
    $user = createUser();
    $list = createList($user, $listQuery, [], 2);
    $child = $list->children()->first();
    $child->update([$itemQuery === 'document' ? 'filename' : 'name' => 'Test']);

    $query = Str::plural($itemQuery);
    $this->be($user)->assertGraphQL([
        "$query(filters: [{ search: [\"Test\"] }])" => [
            'edges' => [['node' => ['id' => $child->global_id]]],
        ],
    ]);
})->with($listItemTypes);

test('list items can have markers', function (string $listQuery, string $itemQuery) {
    $user = createUser();
    $tagGroup = createMarkerGroup($user, ['type' => MarkerType::TAG], 1);
    $tag = $tagGroup->markers()->first();
    $statusGroup = createMarkerGroup($user, ['type' => MarkerType::STATUS], 1);
    $status = $statusGroup->markers()->first();
    $pipelineGroup = createMarkerGroup($user, ['type' => MarkerType::PIPELINE], 3);
    $pipelines = $pipelineGroup->markers;

    $list = createList($user, $listQuery, [], 2);
    $child = $list->children()->first();

    $query = Str::singular($itemQuery);
    $this->be($user)->graphQL(
        "
        mutation(\$id: ID!) {
            tag: setMarker(input: { markerId: \"$tag->global_id\", markableId: \$id }) { code }
            status: setMarker(input: { markerId: \"$status->global_id\", markableId: \$id }) { code }
            pipeline1: setMarker(input: { markerId: \"{$pipelines[2]->global_id}\", markableId: \$id }) { code }
            pipeline2: setMarker(input: { markerId: \"{$pipelines[0]->global_id}\", markableId: \$id }) { code }
        }
        ",
        ['id' => $child->global_id]
    );

    $this->be($user)->graphQL("{
        $query(id: \"$child->global_id\") {
            markerGroups {
                group { id }
                ...on TagMarkerCollection { markers { id } }
                ...on StatusMarkerCollection { marker { id } }
                ...on PipelineMarkerCollection { markers { id } }
            }
        }
    }")->assertJson(['data' => [
        $query => [
            'markerGroups' => [
                ['group' => ['id' => $tagGroup->global_id], 'markers' => [['id' => $tag->global_id]]],
                ['group' => ['id' => $statusGroup->global_id], 'marker' => ['id' => $status->global_id]],
                ['group' => ['id' => $pipelineGroup->global_id], 'markers' => [
                    ['id' => $pipelines[2]->global_id],
                    ['id' => $pipelines[0]->global_id],
                ]],
            ],
        ],
    ]], true);
})->with($listItemTypes);

test('assignees can be fetched on a list item', function (string $listQuery, string $itemQuery) {
    $user = createCollabUser();
    $base = $user->bases->last();
    tenancy()->initialize($base);

    $list = createList($base, $listQuery, [], 1);
    $child = $list->children()->first();

    $group = $base->defaultAssigneeGroup;
    $child->assignees()->attach($base->pivot->id, ['group_id' => $group->id]);

    $this->be($user)->assertGraphQL([
        Str::plural($itemQuery) => [
            'edges' => [
                ['node' => [
                    'id' => $child->global_id,
                    'assigneeGroups' => [[
                        'group' => ['id' => $group->global_id],
                        'assignees' => [[
                            'id' => $base->pivot->global_id,
                            'name' => $base->pivot->displayName(),
                        ]],
                    ]],
                ]],
            ],
        ],
    ]);
})->with($listItemTypes);

test('a list item can be created with markers, associations, and assignees', function (string $listQuery, string $itemQuery, array $fields) use ($featureListMap) {
    $user = createCollabUser();
    $base = $user->bases->last();
    tenancy()->initialize($base);
    $tagGroup = createMarkerGroup($base, ['type' => MarkerType::TAG], 1);
    $tag = $tagGroup->markers()->first();
    $statusGroup = createMarkerGroup($base, ['type' => MarkerType::STATUS], 1);
    $status = $statusGroup->markers()->first();
    $pipelineGroup = createMarkerGroup($base, ['type' => MarkerType::PIPELINE], 1);
    $pipeline = $pipelineGroup->markers()->first();

    $mapping = createMapping($base);
    $mapping->enableFeature($featureListMap[$itemQuery]);
    $item = createItem($mapping);

    $list = createList($base, $listQuery);

    $type = ucfirst($itemQuery);
    $listType = Str::singular($listQuery);
    $this->be($user)->assertGraphQLMutation(
        ["create$type(input: \$input)" => [
            $itemQuery => [
                'name' => 'Test',
            ],
        ]],
        ["input: Create{$type}Input!" => [
            "{$listType}Id" => $list->global_id,
            'markers' => [
                ['groupId' => $tagGroup->global_id, 'markers' => [$tag->global_id]],
                ['groupId' => $statusGroup->global_id, 'markers' => [$status->global_id]],
                ['groupId' => $pipelineGroup->global_id, 'markers' => [$pipeline->global_id]],
            ],
            'associations' => [$item->global_id],
            'assigneeGroups' => [[
                'groupId' => $base->defaultAssigneeGroup->global_id,
                'assignees' => [$base->pivot->global_id],
            ]],
            ...$fields,
        ]],
    );

    tenancy()->initialize($base);
    $child = $list->children->first();
    expect($child->markers)->toHaveCount(3)
        ->get(0)->toHaveKey('id', $tag->id)
        ->get(1)->toHaveKey('id', $status->id)
        ->get(2)->toHaveKey('id', $pipeline->id)
        ->and($child->items)->toHaveCount(1)
        ->get(0)->toHaveKey('id', $item->id)
        ->and($child->assignees)->toHaveCount(1)
        ->get(0)->toHaveKey('id', $base->pivot->id);
})->with($listItemTypesWithParams);

test('a list item can be duplicated', function (string $query, string $itemQuery, array $fields) {
    $user = createUser();
    $list = createList($user, $query, [], 1);
    $child = $list->children()->first();

    $type = ucfirst($itemQuery);
    $this->be($user)->assertGraphQLMutation(
        ["duplicate$type(input: \$input)" => [
            'code' => '200',
        ]],
        ["input: Duplicate{$type}Input!" => ['id' => $child->global_id]]
    );

    $this->assertTrue($list->children()->where('id', '!=', $child->id)->exists());
})->with($listItemTypesWithParams);

test('an item cannot be created with associations from a different space', function (string $listQuery, string $itemQuery, array $fields) use ($featureListMap) {
    $this->withGraphQLExceptionHandling();
    $user = createUser();
    $otherSpace = createSpace($user->firstPersonalBase());

    $mapping = createMapping($user, ['space_id' => $otherSpace]);
    $mapping->enableFeature($featureListMap[$itemQuery]);
    $item = createItem($mapping);

    $list = createList($user, $listQuery);

    $type = ucfirst($itemQuery);
    $listType = Str::singular($listQuery);
    $this->be($user);
    convertToFileRequest("mutation (\$input: Create{$type}Input!) {
        create$type(input: \$input) { code }
    }", ['input' => [
        "{$listType}Id" => $list->global_id,
        'associations' => [$item->global_id],
        ...$fields,
    ]])->assertGraphQLErrorMessage("No results for the requested node(s) [$item->global_id].");
})->with($listItemTypesWithParams);

test('a list item can be updated', function (string $query, string $itemQuery, array $fields) {
    $user = createUser();
    $list = createList($user, $query, [], 1);
    $child = $list->children()->first();

    $type = ucfirst($itemQuery);
    $this->be($user)->assertGraphQLMutation(
        ["update$type(input: \$input)" => [
            $itemQuery => [
                'name' => 'Test',
            ],
        ]],
        ["input: Update{$type}Input!" => ['id' => $child->global_id, ...Arr::except($fields, 'file')]]
    );
})->with($listItemTypesWithParams);

test('moving a list item to another list within the same space', function (string $query, string $itemQuery) {
    $user = createUser();
    $list = createList($user, $query, [], 1);
    $child = $list->children()->first();
    $otherList = createList($user, $query);

    $type = ucfirst($itemQuery);
    $this->be($user)->assertGraphQLMutation(
        ["move$type(input: \$input)" => [
            $itemQuery => [
                $query => ['id' => $otherList->global_id],
            ],
        ]],
        ["input: Move{$type}Input!" => ['id' => $child->global_id, "{$query}Id" => $otherList->global_id]]
    );
    expect($otherList->children)->toHaveCount(1)
        ->and($list->children)->toBeEmpty();
})->with($listItemTypes);

test('a list item can be moved to another list', function (string $query, string $itemQuery) {
    $user = createUser();
    $list = createList($user, $query, [], 1);
    $child = $list->children()->first();
    $otherList = createList($user, $query);

    $type = ucfirst($itemQuery);
    $this->be($user)->assertGraphQLMutation(
        ["update$type(input: \$input)" => [
            $itemQuery => [
                $query => ['id' => $otherList->global_id],
            ],
        ]],
        ["input: Update{$type}Input!" => ['id' => $child->global_id, "{$query}Id" => $otherList->global_id]]
    );
    expect($otherList->children)->toHaveCount(1)
        ->and($list->children)->toBeEmpty();
})->with($listItemTypes);

test('a list item cannot be moved to a list on another space', function (string $query, string $itemQuery) {
    $this->withGraphQLExceptionHandling();
    $user = createUser();
    $list = createList($user, $query, [], 1);
    $child = $list->children()->first();
    $space = createSpace($user->firstPersonalBase());
    $otherList = createList($user, $query, ['space_id' => $space]);

    $type = ucfirst($itemQuery);
    $this->be($user)->graphQL("mutation (\$input: Update{$type}Input!) {
        update$type(input: \$input) { code }
    }", ['input' => [
        'id' => $child->global_id, "{$query}Id" => $otherList->global_id,
    ]])->assertGraphQLErrorMessage("No results for the requested node(s) [$otherList->global_id].");
})->with($listItemTypes);

test('a list item can be deleted', function (string $query, string $itemQuery) {
    $user = createUser();
    $list = createList($user, $query, [], 1);
    $child = $list->children()->first();

    $type = ucfirst($itemQuery);
    $this->be($user)->assertGraphQLMutation(
        ["delete$type(input: \$input)" => [
            'code' => '200',
        ]],
        ["input: Delete{$type}Input!" => ['id' => $child->global_id]]
    );

    expect($child->fresh())->deleted_at->not->toBeNull();
})->with($listItemTypes);

test('a list item can be restored', function (string $query, string $itemQuery) {
    $user = createUser();
    $list = createList($user, $query, [], 1);
    $child = $list->children()->first();
    $child->delete();

    $type = ucfirst($itemQuery);
    $this->be($user)->assertGraphQLMutation(
        ["restore$type(input: \$input)" => [
            'code' => '200',
        ]],
        ["input: Restore{$type}Input!" => ['id' => $child->global_id]]
    );

    expect($child->fresh())->deleted_at->toBeNull();
})->with($listItemTypes);

test('a list item can be fully deleted', function (string $query, string $itemQuery) {
    $user = createUser();
    $list = createList($user, $query, [], 1);
    $child = $list->children()->first();

    $type = ucfirst($itemQuery);
    $this->be($user)->assertGraphQLMutation(
        ["delete$type(input: \$input)" => [
            'code' => '200',
        ]],
        ["input: Delete{$type}Input!" => ['id' => $child->global_id, 'force' => true]]
    );

    expect($child->fresh())->toBeNull();
})->with($listItemTypes);

test('a member can be assigned to a list item', function (string $query, string $itemQuery) {
    $user = createCollabUser();
    $base = $user->bases->last();
    tenancy()->initialize($base);
    $list = createList($base, $query, [], 1);
    $child = $list->children()->first();

    $this->be($user)->assertGraphQLMutation(
        ['updateAssignees(input: $input)' => [
            'code' => '200',
            'node' => [
                'id' => $child->global_id,
                'assigneeGroups' => [[
                    'group' => ['id' => $base->defaultAssigneeGroup->global_id],
                    'assignees' => [['id' => $base->pivot->global_id]],
                ]],
            ],
        ]], ['input: UpdateAssigneesInput!' => [
            'assignableId' => $child->global_id,
            'assigneeGroups' => [[
                'groupId' => $base->defaultAssigneeGroup->global_id,
                'assignees' => [$base->pivot->global_id],
            ]],
        ]]
    );
})->with($listItemTypes);

test('all members can be cleared from a list item', function (string $query, string $itemQuery) {
    $user = createCollabUser();
    $base = $user->bases->last();
    tenancy()->initialize($base);
    $list = createList($base, $query, [], 1);
    $child = $list->children()->first();
    $child->assigneesForGroup($base->defaultAssigneeGroup)->attach([$base->pivot->id => ['group_id' => $base->defaultAssigneeGroup->id]]);

    $this->be($user)->assertGraphQLMutation(
        ['updateAssignees(input: $input)' => [
            'code' => '200',
            'node' => [
                'id' => $child->global_id,
                'assigneeGroups' => new NullFieldWithSubQuery('{ group { id } }', true),
            ],
        ]], ['input: UpdateAssigneesInput!' => [
            'assignableId' => $child->global_id,
            'assigneeGroups' => [],
        ]]
    );
})->with($listItemTypes);
