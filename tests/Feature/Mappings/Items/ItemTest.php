<?php

declare(strict_types=1);

use App\Models\Item;
use App\Models\Image;
use App\Models\Marker;
use App\Models\Mapping;
use App\Core\Pages\PageType;
use Markers\Core\MarkerType;
use Tests\Concerns\TestsFields;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\UsesElasticsearch;
use Illuminate\Support\Facades\Storage;
use Mappings\Core\Mappings\MappingType;
use App\Core\Mappings\FieldFilterOperator;
use App\Core\Mappings\MarkerFilterOperator;
use Mappings\Core\Mappings\Fields\FieldType;
use Illuminate\Database\Events\QueryExecuted;
use App\Core\Mappings\Features\MappingFeatureType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mappings\Core\Mappings\Relationships\RelationshipType;

uses(RefreshDatabase::class);
uses(UsesElasticsearch::class);
uses(TestsFields::class);

uses()->group('elastic');

test('a user can create an item on a mapping', function () {
    $user = createUser();

    Mapping::query()->create([
        'space_id' => $user->firstSpace()->getKey(),
        'name' => 'Photography clients',
        'type' => MappingType::PERSON,
        'fields' => [
            [
                'type' => 'SYSTEM_NAME',
                'name' => 'Full name',
            ],
            [
                'type' => 'NAME',
                'name' => 'Preferred name',
            ],
            [
                'type' => 'EMAIL',
                'name' => 'Email',
            ],
            [
                'type' => 'LINE',
                'name' => 'Phone',
            ],
            [
                'type' => 'PARAGRAPH',
                'name' => 'Addresses',
            ],
            [
                'type' => 'IMAGE',
                'name' => 'Image',
                'options' => [
                    'croppable' => true,
                ],
            ],
            [
                'type' => 'MULTI',
                'name' => 'Position',
                'options' => [
                    'fields' => [
                        [
                            'type' => 'LINE',
                            'name' => 'Job title',
                        ],
                        [
                            'apiName' => 'organization',
                            'type' => 'LINE',
                        ],
                    ],
                ],
            ],
            [
                'type' => 'LINE',
                'name' => 'Birthday',
            ],
        ],
    ]);

    $this->be($user)
        ->assertGraphQLMutation(
            ['items' => ['photographyClients' => [
                'createPhotographyClient(input: $input)' => [
                    'photographyClient' => [
                        'data' => [
                            'birthday' => new NullFieldWithSubQuery('{ fieldValue }'),
                            'email' => ['fieldValue' => 'ld@d.d'],
                            'fullName' => ['fieldValue' => 'Larry'],
                            'phone' => new NullFieldWithSubQuery('{ fieldValue }'),
                            'preferredName' => ['fieldValue' => 'Denna'],
                        ],
                    ],
                ],
            ]]],
            [
                'input: PhotographyClientItemCreateInput!' => [
                    'data' => [
                        'email' => ['fieldValue' => 'ld@d.d'],
                        'fullName' => ['fieldValue' => 'Larry'],
                        'preferredName' => ['fieldValue' => 'Denna'],
                        'phone' => ['fieldValue' => null],
                    ],
                ],
            ]);
});

test('an item can be created with markers', function () {
    $user = createUser();
    $markerGroup = createMarkerGroup($user, [], 1);
    /** @var \App\Models\Marker $marker */
    $marker = $markerGroup->markers->first();
    $mapping = createMapping($user, [
        'name' => 'Items',
        'singular_name' => 'Item',
    ]);
    $mapping->addMarkerGroup([
        'id' => 'markers',
        'name' => 'Markers',
        'group' => $markerGroup,
    ]);

    $this->be($user)->assertGraphQLMutation(
        'items.items.createItem(input: $input).code',
        ['input: ItemItemCreateInput!' => [
            'data' => ['name' => ['fieldValue' => 'Henry']],
            'markers' => [[
                'groupId' => $markerGroup->global_id,
                'markers' => [$marker->global_id],
                'context' => 'markers',
            ]],
        ]]
    );

    $item = $mapping->items->first();
    $item->base->run(function () use ($item, $marker) {
        expect($item->markers->first()->getKey())->toBe($marker->getKey());
        expect($item->markers->first()->pivot->context)->toBe('markers');
    });
});

test('an item must have a name', function () {
    $this->withGraphQLExceptionHandling();
    $user = createUser();

    Mapping::query()->create([
        'space_id' => $user->firstSpace()->getKey(),
        'name' => 'Photography clients',
        'type' => MappingType::PERSON,
        'fields' => [[
            'type' => 'SYSTEM_NAME',
            'name' => 'Full name',
        ]],
    ]);

    $this->be($user)
        ->assertFailedGraphQLMutation(
            ['items' => ['photographyClients' => [
                'createPhotographyClient(input: $input)' => [
                    'photographyClient' => [
                        'data' => [
                            'fullName' => ['fieldValue' => null],
                        ],
                    ],
                ],
            ]]],
            [
                'input: PhotographyClientItemCreateInput!' => [
                    'data' => [
                        'fullName' => ['fieldValue' => null],
                    ],
                ],
            ]
        )
        ->assertJson(['errors' => [['extensions' => ['validation' => [
            'input.data.fullName.fieldValue' => ['The "Full name" field is required.'],
        ]]]]]);
});

test('a user can delete an item from a mapping', function () {
    $user = createUser();
    $mapping = createMapping($user, ['apiName' => 'people', 'apiSingularName' => 'person']);

    $item = createItem($mapping);

    $this->be($user)
        ->assertGraphQLMutation(
            'items.people.deletePerson(input: $input).code',
            ['input: PersonItemDeleteInput!' => [
                'id' => $item->globalId(),
            ]],
        );

    expect($mapping->items()->onlyTrashed()->find($item->getKey()))->not->toBeNull();

    $this->be($user)
        ->assertGraphQLMutation(
            'items.people.deletePerson(input: $input).code',
            ['input: PersonItemDeleteInput!' => [
                'id' => $item->globalId(),
                'force' => true,
            ]],
        );

    expect($mapping->items()->onlyTrashed()->find($item->getKey()))->toBeNull();
});

test('a user can duplicate an item', function () {
    $user = createUser();
    $mapping = createMapping($user, [
        'apiName' => 'people',
        'apiSingularName' => 'person',
        'fields' => [[
            'id' => 'SYSTEM_NAME',
            'type' => 'SYSTEM_NAME',
            'name' => 'Full name',
        ]],
    ]);

    $item = createItem($mapping);

    $this->be($user)
        ->assertGraphQLMutation(
            'items.people.duplicatePerson(input: $input).code',
            ['input: PersonItemDuplicateInput!' => [
                'id' => $item->globalId(),
            ]],
        );

    expect($mapping->items)->toHaveCount(2);
    $duplicate = $mapping->items->last();
    expect($duplicate)
        ->name->toBe('Larry (Copy)')
        ->data->toHaveKey('SYSTEM_NAME', ['_v' => 'Larry (Copy)']);
});

test('a user can duplicate an item with markers, assignees, and relationships', function () {
    $user = createUser();
    $base = $user->firstPersonalBase();
    $markerGroup = createMarkerGroup($user, [], 1);
    $mapping = createMapping($user, [
        'apiName' => 'people',
        'apiSingularName' => 'person',
        'fields' => [[
            'id' => 'SYSTEM_NAME',
            'type' => 'SYSTEM_NAME',
            'name' => 'Full name',
        ]],
    ]);
    $mappingMarkerGroup = $mapping->addMarkerGroup($markerGroup);
    $relation1ThatShouldBeCopied = $mapping->addRelationship(['type' => RelationshipType::MANY_TO_MANY, 'to' => $mapping, 'id' => 'rel1']);
    $relation2ThatShouldBeCopied = $mapping->addRelationship(['type' => RelationshipType::MANY_TO_ONE, 'to' => $mapping, 'id' => 'rel2']);
    $relation1ThatShouldNotBeCopied = $mapping->addRelationship(['type' => RelationshipType::ONE_TO_MANY, 'to' => $mapping, 'id' => 'rel3']);
    $relation2ThatShouldNotBeCopied = $mapping->addRelationship(['type' => RelationshipType::ONE_TO_ONE, 'to' => $mapping, 'id' => 'rel4']);

    $item = createItem($mapping);
    $relatedItem = createItem($mapping);

    $item->markers()->attach($markerGroup->markers->first(), ['context' => $mappingMarkerGroup->id()]);
    $item->assignees()->attach($user, ['group_id' => $base->defaultAssigneeGroup->getKey()]);
    $item->relatedItems($relation1ThatShouldBeCopied)->attach($relatedItem);
    $item->relatedItems($relation2ThatShouldBeCopied)->attach($relatedItem);
    $item->relatedItems($relation1ThatShouldNotBeCopied)->attach($relatedItem);
    $item->relatedItems($relation2ThatShouldNotBeCopied)->attach($relatedItem);

    $this->be($user)
        ->assertGraphQLMutation(
            'items.people.duplicatePerson(input: $input).code',
            ['input: PersonItemDuplicateInput!' => [
                'id' => $item->globalId(),
                'withMarkers' => true,
                'withAssignees' => true,
                'withRelationships' => true,
            ]],
        );
    $this->be($user)
        ->assertGraphQLMutation(
            'items.people.duplicatePerson(input: $input).code',
            ['input: PersonItemDuplicateInput!' => [
                'id' => $item->globalId(),
                'withMarkers' => false,
                'withAssignees' => false,
                'withRelationships' => false,
            ]],
        );

    tenancy()->initialize($mapping->base);
    $duplicateWithExtras = $mapping->items->get(2);
    $duplicateWithoutExtras = $mapping->items->get(3);

    expect($duplicateWithExtras->markers)->toHaveCount(1);
    expect($duplicateWithoutExtras->markers)->toHaveCount(0);

    expect($duplicateWithExtras->assignees)->toHaveCount(1);
    expect($duplicateWithoutExtras->assignees)->toHaveCount(0);

    expect($duplicateWithExtras->relatedItems($relation1ThatShouldBeCopied)->get())->toHaveCount(1);
    expect($duplicateWithExtras->relatedItems($relation2ThatShouldBeCopied)->get())->toHaveCount(1);
    expect($duplicateWithExtras->relatedItems($relation1ThatShouldNotBeCopied)->get())->toHaveCount(0);
    expect($duplicateWithExtras->relatedItems($relation2ThatShouldNotBeCopied)->get())->toHaveCount(0);

    expect($duplicateWithoutExtras->relatedItems($relation1ThatShouldBeCopied)->get())->toHaveCount(0);
    expect($duplicateWithoutExtras->relatedItems($relation2ThatShouldBeCopied)->get())->toHaveCount(0);
    expect($duplicateWithoutExtras->relatedItems($relation1ThatShouldNotBeCopied)->get())->toHaveCount(0);
    expect($duplicateWithoutExtras->relatedItems($relation2ThatShouldNotBeCopied)->get())->toHaveCount(0);
});

test('a user can duplicate an item with image', function () {
    $user = createUser();

    $mapping = $this->createMappingWithField($user, FieldType::IMAGE(), ['primary' => true], 'image');

    $this->be($user)->sendCreateItemRequest($mapping, [
        'image' => ['fieldValue' => [
            'image' => UploadedFile::fake()->image('field.jpg'),
            'url' => '',
            'xOffset' => 0,
            'yOffset' => 0,
            'width' => 3,
            'height' => 2,
            'rotate' => 0,
        ]],
    ], 'image { fieldValue { url } }');

    $item = $mapping->items()->first();

    $this->be($user)
        ->assertGraphQLMutation(
            'items.items.duplicateItem(input: $input).code',
            ['input: ItemItemDuplicateInput!' => [
                'id' => $item->globalId(),
            ]],
        );

    $images = [];
    foreach ($mapping->items as $item) {
        $images[] = Image::findOrFail($item->data['imageId']['_v']['image']);
    }
    expect(count($images))->toBe(2);
    $this->assertEquals($images[0]->size, $images[1]->size);
});

test('a user can duplicate an item with list image', function () {
    $user = createUser();

    $mapping = $this->createMappingWithField($user, FieldType::IMAGE(), ['primary' => false, 'list' => true], 'imageList');

    $this->be($user)->sendCreateItemRequest($mapping, [
        'imageList' => [
            'listValue' => [
                ['fieldValue' => [
                    'image' => UploadedFile::fake()->image('field1.jpg'),
                    'url' => '',
                    'xOffset' => 0,
                    'yOffset' => 0,
                    'width' => 9,
                    'height' => 5,
                    'rotate' => 0,
                ], 'main' => false],
                ['fieldValue' => [
                    'image' => UploadedFile::fake()->image('field2.jpg'),
                    'url' => '',
                    'xOffset' => 0,
                    'yOffset' => 0,
                    'width' => 99,
                    'height' => 55,
                    'rotate' => 0,
                ], 'main' => false],
            ],
        ],
    ], 'imageList { listValue { fieldValue { url }, fieldValue { url } } }');

    $item = $mapping->items()->first();

    $this->be($user)
        ->assertGraphQLMutation(
            'items.items.duplicateItem(input: $input).code',
            ['input: ItemItemDuplicateInput!' => [
                'id' => $item->globalId(),
            ]],
        );

    $listImages = [];
    foreach ($mapping->items as $k => $item) {
        $foundImages = findImages($item->data);
        foreach ($foundImages as $imageId) {
            $listImages[$k][] = Image::findOrFail($imageId);
        }
    }

    expect(count($listImages))->toBe(2);
    $this->assertEquals($listImages[0][0]->size, $listImages[1][0]->size);
    $this->assertEquals($listImages[0][1]->size, $listImages[1][1]->size);
});

test('a user can duplicate an item with image inside multi field', function () {
    $user = createUser();

    $mapping = $this->createMappingWithField($user, FieldType::MULTI(), ['fields' => [
        ['id' => 'imageFieldId', 'name' => 'imageField', 'type' => FieldType::IMAGE()],
    ]], 'multiField');

    // Fake file upload
    Storage::fake('images');
    $this->be($user)->sendCreateItemRequest($mapping,
        [
            'multiField' => [
                'fieldValue' => [
                    'imageField' => [
                        'fieldValue' => [
                            'image' => UploadedFile::fake()->image('field.jpg'),
                            'url' => '',
                            'xOffset' => 0,
                            'yOffset' => 0,
                            'width' => 3,
                            'height' => 2,
                            'rotate' => 0,
                        ],
                    ],
                ],
            ],
        ], 'multiField { fieldValue { imageField { fieldValue { url } } } }'

    );

    $item = $mapping->items()->first();

    $this->be($user)
        ->assertGraphQLMutation(
            'items.items.duplicateItem(input: $input).code',
            ['input: ItemItemDuplicateInput!' => [
                'id' => $item->globalId(),
            ]],
        );

    $images = [];
    foreach ($mapping->items as $item) {
        $images[] = Image::findOrFail($item->data['multiFieldId']['_v']['imageFieldId']['_v']['image']);
    }
    expect(count($images))->toBe(2);
    $this->assertEquals($images[0]->size, $images[1]->size);
});

test('a user can duplicate an item with image inside a list multi field', function () {
    $user = createUser();

    $mapping = $this->createMappingWithField($user, FieldType::MULTI(), ['list' => true, 'fields' => [
        ['id' => 'imageFieldId', 'name' => 'imageField', 'type' => FieldType::IMAGE()],
    ]], 'multiField');

    // Fake file upload
    Storage::fake('images');
    $this->be($user)->sendCreateItemRequest($mapping,
        [
            'multiField' => ['listValue' => [[
                'fieldValue' => [
                    'imageField' => [
                        'fieldValue' => [
                            'image' => UploadedFile::fake()->image('field.jpg'),
                            'url' => '',
                            'xOffset' => 0,
                            'yOffset' => 0,
                            'width' => 3,
                            'height' => 2,
                            'rotate' => 0,
                        ],
                    ],
                ],
            ]]],
        ], 'multiField { listValue { fieldValue { imageField { fieldValue { url } } } } }'
    );

    $item = $mapping->items()->first();

    $this->be($user)
        ->assertGraphQLMutation(
            'items.items.duplicateItem(input: $input).code',
            ['input: ItemItemDuplicateInput!' => [
                'id' => $item->globalId(),
            ]],
        );

    $duplicateItem = $mapping->items()->latest('id')->first();
    $originalImage = Image::findOrFail($item->data['multiFieldId']['_c'][0]['_v']['imageFieldId']['_v']['image']);
    $duplicateImage = Image::findOrFail($duplicateItem->data['multiFieldId']['_c'][0]['_v']['imageFieldId']['_v']['image']);
    expect($originalImage->isNot($duplicateImage))->toBeTrue();
});

test('a user can duplicate an item with image inside multi field inside multi field', function () {
    $user = createUser();

    $mapping = $this->createMappingWithField($user, FieldType::MULTI(), [
        'fields' => [[
            'id' => 'secondMultiFieldId',
            'name' => 'secondMultiField',
            'type' => FieldType::MULTI(),
            'options' => [
                'fields' => [[
                    'id' => 'imageFieldId',
                    'name' => 'imageField',
                    'type' => FieldType::IMAGE(),
                ]],
            ],
        ]],
    ], 'firstMultiField');

    // Fake file upload
    Storage::fake('images');
    $this->be($user)->sendCreateItemRequest($mapping, [
        'firstMultiField' => [
            'fieldValue' => [
                'secondMultiField' => [
                    'fieldValue' => [
                        'imageField' => [
                            'fieldValue' => [
                                'image' => UploadedFile::fake()->image('field.jpg'),
                                'url' => '',
                                'xOffset' => 0,
                                'yOffset' => 0,
                                'width' => 3,
                                'height' => 2,
                                'rotate' => 0,
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ], 'firstMultiField { fieldValue { secondMultiField { fieldValue { imageField { fieldValue { url } } } } } }'
    );

    $item = $mapping->items()->first();

    $this->be($user)
        ->assertGraphQLMutation(
            'items.items.duplicateItem(input: $input).code',
            ['input: ItemItemDuplicateInput!' => [
                'id' => $item->globalId(),
            ]],
        );

    $images = [];
    foreach ($mapping->items as $item) {
        $images[] = Image::findOrFail($item->data['firstMultiFieldId']['_v']['secondMultiFieldId']['_v']['imageFieldId']['_v']['image']);
    }
    expect(count($images))->toBe(2);
    $this->assertEquals($images[0]->size, $images[1]->size);
});

test('items can be grouped by markers', function () {
    $user = createUser();

    $markerGroup = createMarkerGroup($user, [], 1);
    /** @var \App\Models\Marker $marker */
    $marker = $markerGroup->markers()->first();

    $mapping = createMapping($user, [
        'space_id' => $user->firstSpace()->getKey(),
        'name' => 'People',
        'type' => MappingType::PERSON,
        'fields' => [
            [
                'id' => 'name',
                'type' => 'SYSTEM_NAME',
                'name' => 'Full name',
            ],
        ],
    ]);

    $mapping->addMarkerGroup($markerGroup);

    /** @var \App\Models\Item $item1 */
    $item1 = createItem($mapping, ['name' => ['_v' => 'Harry']]);
    $item2 = createItem($mapping, ['name' => ['_v' => 'Bill']]);

    $item1->addMarker($marker);

    $this->be($user)->assertGraphQL(
        ['groupedItems' => [
            "people(group: \"marker:$markerGroup->global_id\")" => [
                'groups' => [
                    [
                        'groupHeader' => $marker->global_id,
                        'edges' => [['node' => ['id' => $item1->global_id]]],
                    ],
                    [
                        'groupHeader' => null,
                        'edges' => [['node' => ['id' => $item2->global_id]]],
                    ],
                ],
            ],
        ]]
    );
});

test('items can be grouped by priority', function () {
    $user = createUser();

    $mapping = createMapping($user, [
        'space_id' => $user->firstSpace()->getKey(),
        'name' => 'People',
        'type' => MappingType::PERSON,
        'features' => [['val' => MappingFeatureType::PRIORITIES]],
    ]);

    $item1 = createItem($mapping, ['name' => ['_v' => 'Harry']]);
    $item1->update(['priority' => 5]);
    $item2 = createItem($mapping, ['name' => ['_v' => 'Bill']]);

    $this->be($user)->assertGraphQL(
        ['groupedItems' => [
            'people(group: "PRIORITY")' => [
                'groups' => [
                    ['groupHeader' => '0', 'edges' => [['node' => ['id' => $item2->global_id]]]],
                    ['groupHeader' => '1', 'edges' => []],
                    ['groupHeader' => '3', 'edges' => []],
                    ['groupHeader' => '5', 'edges' => [['node' => ['id' => $item1->global_id]]]],
                    ['groupHeader' => '9', 'edges' => []],
                ],
            ],
        ]]
    );
});

test('items can be grouped by favorite', function () {
    $user = createUser();

    $mapping = createMapping($user, [
        'space_id' => $user->firstSpace()->getKey(),
        'name' => 'People',
        'type' => MappingType::PERSON,
        'features' => [['val' => MappingFeatureType::FAVORITES]],
    ]);

    $item1 = createItem($mapping, ['name' => ['_v' => 'Harry']]);
    $item1->update(['favorited_at' => now()]);
    $item2 = createItem($mapping, ['name' => ['_v' => 'Bill']]);

    $this->be($user)->assertGraphQL(
        ['groupedItems' => [
            'people(group: "FAVORITES")' => [
                'groups' => [
                    ['groupHeader' => '1', 'edges' => [['node' => ['id' => $item1->global_id]]]],
                    ['groupHeader' => '0', 'edges' => [['node' => ['id' => $item2->global_id]]]],
                ],
            ],
        ]]
    );
});

test('items fields can be updated individually', function () {
    $user = createUser();

    $mapping = createMapping($user, [
        'name' => 'Photography clients',
        'type' => MappingType::PERSON,
        'fields' => [
            [
                'id' => 'nameId',
                'type' => 'SYSTEM_NAME',
                'name' => 'Full name',
            ],
            [
                'id' => 'otherNameId',
                'type' => 'NAME',
                'name' => 'Preferred name',
            ],
            [
                'id' => 'emailId',
                'type' => 'EMAIL',
                'name' => 'Email',
            ],
            [
                'type' => 'LINE',
                'name' => 'Phone',
            ],
            [
                'type' => 'PARAGRAPH',
                'name' => 'Addresses',
            ],
            [
                'type' => 'IMAGE',
                'name' => 'Image',
                'options' => [
                    'croppable' => true,
                ],
            ],
            [
                'type' => 'MULTI',
                'name' => 'Position',
                'options' => [
                    'fields' => [
                        [
                            'type' => 'LINE',
                            'name' => 'Job title',
                        ],
                        [
                            'apiName' => 'organization',
                            'type' => 'LINE',
                        ],
                    ],
                ],
            ],
            [
                'type' => 'LINE',
                'name' => 'Birthday',
            ],
        ],
    ]);

    $item = createItem($mapping, [
        'emailId' => ['_v' => 'ld@d.d'],
        'nameId' => ['_v' => 'Larry'],
        'otherNameId' => ['_v' => 'Denna'],
    ]);

    $this->be($user)
        ->assertGraphQLMutation(
            ['items' => ['photographyClients' => [
                'updatePhotographyClient(input: $input)' => [
                    'photographyClient' => [
                        'data' => [
                            'birthday' => new NullFieldWithSubQuery('{ fieldValue }'),
                            'email' => ['fieldValue' => 'ld@d.d'],
                            'fullName' => ['fieldValue' => 'Toby'],
                            'phone' => new NullFieldWithSubQuery('{ fieldValue }'),
                            'preferredName' => ['fieldValue' => 'Denna'],
                        ],
                    ],
                ],
            ]]],
            ['input: PhotographyClientItemUpdateInput!' => [
                'id' => $item->global_id,
                'data' => [
                    'fullName' => ['fieldValue' => 'Toby'],
                ],
            ]]
        );
});

test('an item can be favorited', function () {
    $user = createUser();

    $mapping = createMapping($user, [
        'name' => 'Photography clients',
        'features' => [['val' => MappingFeatureType::FAVORITES]],
    ]);

    $item = createItem($mapping);

    $this->be($user)
        ->assertGraphQLMutation(
            ['items' => ['photographyClients' => [
                'updatePhotographyClient(input: $input)' => [
                    'photographyClient' => [
                        'isFavorite' => true,
                    ],
                ],
            ]]],
            ['input: PhotographyClientItemUpdateInput!' => [
                'id' => $item->global_id,
                'isFavorite' => true,
            ]]
        );

    expect($item->fresh()->favorited_at)->not->toBeNull();

    $this->be($user)->assertGraphQLMutation(
        ['items' => ['photographyClients' => [
            'updatePhotographyClient(input: $input)' => [
                'photographyClient' => [
                    'isFavorite' => false,
                ],
            ],
        ]]],
        ['input: PhotographyClientItemUpdateInput!' => [
            'id' => $item->global_id,
            'isFavorite' => false,
        ]]
    );

    expect($item->fresh()->favorited_at)->toBeNull();
});

test('a field can just have a label', function () {
    $user = createUser();

    createMapping($user, [
        'name' => 'Photography clients',
        'fields' => [
            [
                'type' => 'SYSTEM_NAME',
                'name' => 'Full name',
            ],
            [
                'type' => 'LINE',
                'name' => 'Test',
                'options' => ['labeled' => ['freeText' => false, 'labels' => ['a', 'b']]],
            ],
        ],
    ]);

    $this->be($user)
        ->assertGraphQLMutation(
            ['items', 'photographyClients', 'createPhotographyClient(input: $input)', 'code'],
            ['input: PhotographyClientItemCreateInput!' => [
                'data' => ['fullName' => ['fieldValue' => 'Larry'], 'test' => ['label' => '1']],
            ]]
        );
});

test('item pages do not include pages that filter out the item', function () {
    $user = createUser();

    /** @var \App\Models\MarkerGroup $tagGroup */
    $tagGroup = $user->firstPersonalBase()->markerGroups()->create(['name' => 'Test', 'type' => MarkerType::TAG]);
    /** @var \App\Models\Marker $tag */
    $tag = $tagGroup->markers()->create(['name' => 'Test tag']);

    $mapping = createMapping($user, [
        'name' => 'Photography clients',
        'type' => MappingType::ITEM,
        'fields' => [
            ['type' => 'SYSTEM_NAME', 'name' => 'Full name'],
            ['id' => 'test', 'type' => 'SELECT', 'name' => 'Test', 'options' => ['valueOptions' => ['a', 'b']]],
            ['id' => 'test2', 'type' => 'SELECT', 'name' => 'Labeled', 'options' => ['labeled' => ['freeText' => true], 'valueOptions' => ['a', 'b']]],
            ['id' => 'test3', 'type' => 'SELECT', 'name' => 'List', 'options' => ['list' => true, 'valueOptions' => ['a', 'b']]],
            ['id' => 'test4', 'type' => 'SELECT', 'name' => 'List Labeled', 'options' => ['list' => true, 'labeled' => ['freeText' => true], 'valueOptions' => ['a', 'b']]],
            ['id' => 'test5', 'type' => 'SELECT', 'name' => 'Multi Select', 'options' => ['multiSelect' => true, 'valueOptions' => ['a', 'b']]],
            ['id' => 'test6', 'type' => 'SELECT', 'name' => 'Labeled Multi Select', 'options' => ['multiSelect' => true, 'labeled' => ['freeText' => true], 'valueOptions' => ['a', 'b']]],
            ['id' => 'test7', 'type' => 'SELECT', 'name' => 'List Multi Select', 'options' => ['multiSelect' => true, 'list' => true, 'valueOptions' => ['a', 'b']]],
            ['id' => 'test8', 'type' => 'SELECT', 'name' => 'List Labeled Multi Select', 'options' => ['multiSelect' => true, 'list' => true, 'labeled' => ['freeText' => true], 'valueOptions' => ['a', 'b']]],
        ],
        'marker_groups' => [['group' => $tagGroup]],
    ]);
    $space = $mapping->space;

    $normalPage = $space->pages()->create([
        'path' => 'Normal',
        'mapping_id' => $mapping->id,
        'type' => PageType::ENTITIES,
    ]);
    $taggedPage = $space->pages()->create([
        'path' => 'Tagged',
        'mapping_id' => $mapping->id,
        'type' => PageType::ENTITIES,
        'markerFilters' => [[
            'operator' => MarkerFilterOperator::IS,
            'markerId' => $tag->global_id,
        ]],
    ]);
    $fieldPage = $space->pages()->create([
        'path' => 'Field',
        'mapping_id' => $mapping->id,
        'type' => PageType::ENTITIES,
        'fieldFilters' => [[
            'fieldId' => 'test',
            'operator' => FieldFilterOperator::IS,
            'match' => 1,
        ]],
    ]);
    $labeledFieldPage = $space->pages()->create([
        'path' => 'Field',
        'mapping_id' => $mapping->id,
        'type' => PageType::ENTITIES,
        'fieldFilters' => [[
            'fieldId' => 'test2',
            'operator' => FieldFilterOperator::IS,
            'match' => 1,
        ]],
    ]);
    $listFieldPage = $space->pages()->create([
        'path' => 'Field',
        'mapping_id' => $mapping->id,
        'type' => PageType::ENTITIES,
        'fieldFilters' => [[
            'fieldId' => 'test3',
            'operator' => FieldFilterOperator::IS,
            'match' => 1,
        ]],
    ]);
    $listLabeledFieldPage = $space->pages()->create([
        'path' => 'Field',
        'mapping_id' => $mapping->id,
        'type' => PageType::ENTITIES,
        'fieldFilters' => [[
            'fieldId' => 'test4',
            'operator' => FieldFilterOperator::IS,
            'match' => 1,
        ]],
    ]);
    $multiSelectFieldPage = $space->pages()->create([
        'path' => 'Field',
        'mapping_id' => $mapping->id,
        'type' => PageType::ENTITIES,
        'fieldFilters' => [[
            'fieldId' => 'test5',
            'operator' => FieldFilterOperator::IS,
            'match' => 1,
        ]],
    ]);
    $labeledMultiSelectFieldPage = $space->pages()->create([
        'path' => 'Field',
        'mapping_id' => $mapping->id,
        'type' => PageType::ENTITIES,
        'fieldFilters' => [[
            'fieldId' => 'test6',
            'operator' => FieldFilterOperator::IS,
            'match' => 1,
        ]],
    ]);
    $listMultiSelectFieldPage = $space->pages()->create([
        'path' => 'Field',
        'mapping_id' => $mapping->id,
        'type' => PageType::ENTITIES,
        'fieldFilters' => [[
            'fieldId' => 'test7',
            'operator' => FieldFilterOperator::IS,
            'match' => 1,
        ]],
    ]);
    $listLabeledMultiSelectFieldPage = $space->pages()->create([
        'path' => 'Field',
        'mapping_id' => $mapping->id,
        'type' => PageType::ENTITIES,
        'fieldFilters' => [[
            'fieldId' => 'test8',
            'operator' => FieldFilterOperator::IS,
            'match' => 1,
        ]],
    ]);

    $singleItem = createItem($mapping);
    $genericItem = createItem($mapping);
    $taggedItem = tap(createItem($mapping), fn (Item $item) => $item->markers()->attach($tag));
    $fieldItem = createItem($mapping, ['fullName' => ['_v' => 'Denna'], 'test' => ['_v' => 1]]);
    $labeledFieldItem = createItem($mapping, ['fullName' => ['_v' => 'Lydia'], 'test2' => ['_v' => 1, '_l' => 'label']]);
    $listFieldItem = createItem($mapping, ['fullName' => ['_v' => 'Margaret'], 'test3' => ['_c' => [['_v' => 1]]]]);
    $listLabeledFieldItem = createItem($mapping, ['fullName' => ['_v' => 'Henry'], 'test4' => ['_c' => [['_v' => 1, '_l' => 'label']]]]);
    $multiSelectFieldItem = createItem($mapping, ['fullName' => ['_v' => 'Benny'], 'test5' => ['_v' => [1]]]);
    $labeledMultiSelectFieldItem = createItem($mapping, ['fullName' => ['_v' => 'Geoff'], 'test6' => ['_v' => [1], '_l' => 'label']]);
    $listMultiSelectFieldItem = createItem($mapping, ['fullName' => ['_v' => 'Shana'], 'test7' => ['_c' => [['_v' => [1]]]]]);
    $listLabeledMultiSelectFieldItem = createItem($mapping, ['fullName' => ['_v' => 'Carol'], 'test8' => ['_c' => [['_v' => [1], '_l' => 'label']]]]);

    $singleRecordPage = $space->pages()->create([
        'path' => 'Single',
        'mapping_id' => $mapping->id,
        'type' => PageType::ENTITY,
        'entityId' => $singleItem->id,
    ]);

    $this->be($user)
        ->assertGraphQL(['items' => ['photographyClients' => [
            'edges' => [
                ['node' => [
                    'id' => $listLabeledMultiSelectFieldItem->global_id,
                    'pages' => [['id' => $normalPage->global_id], ['id' => $listLabeledMultiSelectFieldPage->global_id]],
                ]],
                ['node' => [
                    'id' => $listMultiSelectFieldItem->global_id,
                    'pages' => [['id' => $normalPage->global_id], ['id' => $listMultiSelectFieldPage->global_id]],
                ]],
                ['node' => [
                    'id' => $labeledMultiSelectFieldItem->global_id,
                    'pages' => [['id' => $normalPage->global_id], ['id' => $labeledMultiSelectFieldPage->global_id]],
                ]],
                ['node' => [
                    'id' => $multiSelectFieldItem->global_id,
                    'pages' => [['id' => $normalPage->global_id], ['id' => $multiSelectFieldPage->global_id]],
                ]],
                ['node' => [
                    'id' => $listLabeledFieldItem->global_id,
                    'pages' => [['id' => $normalPage->global_id], ['id' => $listLabeledFieldPage->global_id]],
                ]],
                ['node' => [
                    'id' => $listFieldItem->global_id,
                    'pages' => [['id' => $normalPage->global_id], ['id' => $listFieldPage->global_id]],
                ]],
                ['node' => [
                    'id' => $labeledFieldItem->global_id,
                    'pages' => [['id' => $normalPage->global_id], ['id' => $labeledFieldPage->global_id]],
                ]],
                ['node' => [
                    'id' => $fieldItem->global_id,
                    'pages' => [['id' => $normalPage->global_id], ['id' => $fieldPage->global_id]],
                ]],
                ['node' => [
                    'id' => $taggedItem->global_id,
                    'pages' => [['id' => $normalPage->global_id], ['id' => $taggedPage->global_id]],
                ]],
                ['node' => [
                    'id' => $genericItem->global_id,
                    'pages' => [['id' => $normalPage->global_id]],
                ]],
                [
                    'node' => [
                        'id' => $singleItem->globalId(),
                        'pages' => [['id' => $normalPage->global_id], ['id' => $singleRecordPage->global_id]],
                    ],
                ],
            ],
        ]]]);
});

test('feature queries on items are optimised', function () {
    $this->rethrowGraphQLErrors();
    $user = createUser();

    $mapping = createMapping($user, [
        'space_id' => $user->firstSpace()->getKey(),
        'name' => 'People',
        'type' => MappingType::PERSON,
        'fields' => [
            [
                'id' => 'name',
                'type' => 'SYSTEM_NAME',
                'name' => 'Full name',
            ],
        ],
    ]);
    $mapping->enableFeature(MappingFeatureType::NOTES);

    /** @var \App\Models\Item $item1 */
    $item1 = createItem($mapping, ['name' => ['_v' => 'Harry']]);
    /** @var \App\Models\Item $item2 */
    $item2 = createItem($mapping, ['name' => ['_v' => 'Bill']]);

    $list = createList($user, 'notebooks', [], 2);
    $item1->notes()->attach($list->children->first());
    $item2->notes()->attach($list->children->last());
    $list->children->last()->update(['favorited_at' => now()]);

    $notesQueriesCount = 0;

    DB::listen(function (QueryExecuted $e) use (&$notesQueriesCount) {
        if (str_contains($e->sql, 'from "notes"')) {
            $notesQueriesCount++;
        }
    });
    $this->be($user)->assertGraphQL(
        ['items' => [
            'people' => [
                'edges' => [
                    ['node' => [
                        'id' => $item2->global_id,
                        'features' => [
                            'notes' => [
                                'edges' => [['node' => ['id' => $list->children->last()->global_id]]],
                                'pageInfo' => ['total' => 1],
                            ],
                            'favoriteNotes: notes(filters: [{ isFavorited: true }])' => [
                                'edges' => [['node' => ['id' => $list->children->last()->global_id]]],
                                'pageInfo' => ['total' => 1],
                            ],
                        ],
                    ]],
                    ['node' => [
                        'id' => $item1->global_id,
                        'features' => [
                            'notes' => [
                                'edges' => [['node' => ['id' => $list->children->first()->global_id]]],
                                'pageInfo' => ['total' => 1],
                            ],
                            'favoriteNotes: notes(filter: FAVORITES)' => [
                                'edges' => [],
                                'pageInfo' => ['total' => 0],
                            ],
                        ],
                    ]],
                ]],
        ]]
    );

    // One query for the count and one for the actual notes.
    expect($notesQueriesCount)->toBe(2);
});

test('item associations are only resolved if the feature is enabled', function () {
    $user = createUser();
    $mapping = createMapping($user);
    $item = createItem($mapping);
    $todoList = createList($user, 'todoList', [], 1);
    /** @var \App\Models\Todo $todo */
    $todo = $todoList->children->first();
    $mapping->enableFeature(MappingFeatureType::TODOS);

    $todo->items()->attach($item);

    $this->be($user)->assertGraphQL([
        "todo(id: \"$todo->global_id\")" => [
            'associations' => [['id' => $item->global_id]],
        ],
    ]);

    $mapping->disableFeature(MappingFeatureType::TODOS);
    $user->firstPersonalBase()->unsetRelation('mappings');

    $this->be($user)->graphQL(
        "query { todo(id: \"$todo->global_id\") {
            associations { id }
        } }"
    )->assertJsonCount(0, 'data.todo.associations');
});

test('items can be filtered by relationship', function () {
    $user = createUser();

    $parentMapping = createMapping($user, [
        'space_id' => $user->firstSpace()->getKey(),
        'name' => 'Parents',
        'type' => MappingType::PERSON,
    ]);

    $childMapping = createMapping($user, [
        'space_id' => $user->firstSpace()->getKey(),
        'name' => 'Children',
        'type' => MappingType::PERSON,
    ]);

    $relationship = $parentMapping->addRelationship(['name' => 'Children', 'type' => RelationshipType::ONE_TO_MANY, 'to' => $childMapping]);

    $parent = createItem($parentMapping, ['name' => ['_v' => 'Harry']]);
    $children = createItem($childMapping, ['name' => ['_v' => 'Bill']], 4);

    foreach ($children as $child) {
        $parent->relatedItems($relationship)->attach($child);
    }

    $cursor = $this->be($user)->assertGraphQL(
        ['items' => [
            "children(first: 2, forRelation: {relationId: \"$relationship->id\", itemId: \"$parent->global_id\"})" => [
                'edges' => [
                    ['node' => ['id' => $children->get(3)->global_id]],
                    ['node' => ['id' => $children->get(2)->global_id]],
                ],
                'pageInfo' => [
                    'endCursor' => new Ignore,
                ],
            ],
        ]]
    )->json('data.items.children.pageInfo.endCursor');

    $this->be($user)->assertGraphQL(
        ['items' => [
            "children(first: 2, after: \"$cursor\", forRelation: {relationId: \"$relationship->id\", itemId: \"$parent->global_id\"})" => [
                'edges' => [
                    ['node' => ['id' => $children->get(1)->global_id]],
                    ['node' => ['id' => $children->get(0)->global_id]],
                ],
            ],
        ]]
    );
});

describe('es tests', function () {
    test('items can be grouped by marker and searched', function () {
        $user = createUser();

        $markerGroup = createMarkerGroup($user, [], 1);
        /** @var \App\Models\Marker $marker */
        $marker = $markerGroup->markers()->first();

        $mapping = createMapping($user, [
            'space_id' => $user->firstSpace()->getKey(),
            'name' => 'People',
            'type' => MappingType::PERSON,
        ]);

        $mapping->addMarkerGroup($markerGroup);

        $item1 = createItem($mapping, ['name' => ['_v' => 'Harry']]);
        $items = createItem($mapping, ['name' => ['_v' => 'Harry2']], 6);
        createItem($mapping, ['name' => ['_v' => 'Bill']]);

        $item1->addMarker($marker);

        $cursor = $this->be($user)->assertGraphQL(
            ['groupedItems' => [
                "people(first: 3, group: \"marker:$markerGroup->global_id\" filters: [{search: \"Harry\"}])" => [
                    'groups' => [
                        [
                            'groupHeader' => $marker->global_id,
                            'edges' => [['node' => ['id' => $item1->global_id]]],
                            'pageInfo' => ['endCursor' => new Ignore],
                        ],
                        [
                            'groupHeader' => null,
                            'edges' => [
                                ['node' => ['id' => $items[5]->global_id]],
                                ['node' => ['id' => $items[4]->global_id]],
                                ['node' => ['id' => $items[3]->global_id]],
                            ],
                            'pageInfo' => ['endCursor' => new Ignore],
                        ],
                    ],
                ],
            ]]
        )->json('data.groupedItems.people.groups.1.pageInfo.endCursor');

        $this->be($user)->assertGraphQL(
            ['groupedItems' => [
                "people(first: 3, after: \"$cursor\", group: \"marker:$markerGroup->global_id\" filters: [{search: \"Harry\"}], includeGroups: [null])" => [
                    'groups' => [
                        [
                            'groupHeader' => null,
                            'edges' => [
                                ['node' => ['id' => $items[2]->global_id]],
                                ['node' => ['id' => $items[1]->global_id]],
                                ['node' => ['id' => $items[0]->global_id]],
                            ],
                        ],
                    ],
                ],
            ]]
        );
    });

    test('items can be grouped by their fields', function () {
        $this->rethrowGraphQLErrors();
        $user = createUser();

        $mapping = createMapping($user, [
            'name' => 'People',
            'type' => MappingType::PERSON,
            'fields' => [
                [
                    'id' => 'name',
                    'type' => 'SYSTEM_NAME',
                    'name' => 'Full name',
                ],
                [
                    'id' => 'select',
                    'type' => 'SELECT',
                    'name' => 'Select',
                    'options' => ['valueOptions' => ['a', 'b']],
                ],
            ],
        ]);

        $item1 = createItem($mapping, ['name' => ['_v' => 'Aaron'], 'select' => ['_v' => 0]]);
        $item2 = createItem($mapping, ['name' => ['_v' => 'Barbara'], 'select' => ['_v' => 1]]);
        $item3 = createItem($mapping, ['name' => ['_v' => 'Claire'], 'select' => ['_v' => 0]]);
        $item4 = createItem($mapping, ['name' => ['_v' => 'Donnie'], 'select' => ['_v' => 1]]);
        $item5 = createItem($mapping, ['name' => ['_v' => 'Ellie'], 'select' => ['_v' => 0]]);
        $item6 = createItem($mapping, ['name' => ['_v' => 'Felicity'], 'select' => ['_v' => 1]]);

        $cursor = $this->be($user)
            ->assertGraphQL(
                ['groupedItems' => [
                    'people(group: "field:select", first: 2, orderBy: [{ field: "NAME", direction: DESC }])' => [
                        'groups' => [
                            [
                                'groupHeader' => '0',
                                'edges' => [
                                    ['node' => ['id' => $item5->global_id]],
                                    ['node' => ['id' => $item3->global_id]],
                                ],
                                'pageInfo' => ['endCursor' => new Ignore],
                            ],
                            [
                                'groupHeader' => '1',
                                'edges' => [
                                    ['node' => ['id' => $item6->global_id]],
                                    ['node' => ['id' => $item4->global_id]],
                                ],
                                'pageInfo' => ['endCursor' => new Ignore],
                            ],
                        ],
                    ],
                ]],
            )->json('data.groupedItems.people.groups.1.pageInfo.endCursor');

        $this->be($user)->assertGraphQL(
            ['groupedItems' => [
                "people(first: 2, after: \"$cursor\", group: \"field:select\", includeGroups: [\"1\"], orderBy: [{ field: \"NAME\", direction: DESC }])" => [
                    'groups' => [
                        [
                            'groupHeader' => '1',
                            'edges' => [
                                ['node' => ['id' => $item2->global_id]],
                            ],
                        ],
                    ],
                ],
            ]]
        );
    });

    test('items can be filtered by their fields', function () {
        $this->rethrowGraphQLErrors();
        $user = createUser();

        $mapping = createMapping($user, [
            'name' => 'People',
            'type' => MappingType::PERSON,
            'fields' => [
                [
                    'id' => 'name',
                    'type' => 'SYSTEM_NAME',
                    'name' => 'Full name',
                ],
                [
                    'id' => 'otherName',
                    'type' => 'NAME',
                    'name' => 'Other name',
                ],
            ],
        ]);

        $item1 = createItem($mapping, [
            'name' => ['_v' => 'Harry'],
            'otherName' => ['_v' => 'Bill'],
        ]);
        createItem($mapping, [
            'name' => ['_v' => 'Bill'],
            'otherName' => ['_v' => 'Harry'],
        ]);

        $this->be($user)
            ->assertGraphQL(
                ['items' => [
                    'people(filters: [{fields: [{ fieldId: "name", operator: IS, match: "\"Harry\"" }]}])' => [
                        'edges' => [['node' => ['id' => $item1->global_id]]],
                    ],
                ]],
            );
    });

    test('all items can be queried', function () {
        $this->rethrowGraphQLErrors();
        $user = createUser();

        $mapping1 = createMapping($user, [
            'name' => 'People',
            'type' => MappingType::PERSON,
            'fields' => [[
                'id' => 'name',
                'type' => 'SYSTEM_NAME',
                'name' => 'Full name',
            ]],
        ]);
        $mapping2 = createMapping($user, [
            'name' => 'Objects',
            'type' => MappingType::ITEM,
            'fields' => [[
                'id' => 'name',
                'type' => 'SYSTEM_NAME',
                'name' => 'Name',
            ]],
        ]);

        $items1 = createItem($mapping1, ['name' => ['_v' => 'Harry']], 2);
        $items2 = createItem($mapping2, ['name' => ['_v' => 'Thing']], 2);

        $this->be($user)->assertGraphQL(['allItems' => ['edges' => [
            ['node' => ['id' => $items2->get(1)->global_id]],
            ['node' => ['id' => $items2->get(0)->global_id]],
            ['node' => ['id' => $items1->get(1)->global_id]],
            ['node' => ['id' => $items1->get(0)->global_id]],
        ]]]);
    });

    test('items from other accounts cannot be queried', function () {
        $this->rethrowGraphQLErrors();
        $otherUser = createUser();

        $otherMapping = createMapping($otherUser, [
            'name' => 'People',
            'type' => MappingType::PERSON,
            'fields' => [[
                'id' => 'name',
                'type' => 'SYSTEM_NAME',
                'name' => 'Full name',
            ]],
        ]);

        createItem($otherMapping, ['name' => ['_v' => 'Harry']], 2);

        $user = createUser();
        $mapping = createMapping($user, [
            'name' => 'Objects',
            'type' => MappingType::ITEM,
            'fields' => [[
                'id' => 'name',
                'type' => 'SYSTEM_NAME',
                'name' => 'Name',
            ]],
        ]);

        $items = createItem($mapping, ['name' => ['_v' => 'Thing']], 2);

        $this->be($user)->assertGraphQL(['allItems' => ['edges' => [
            ['node' => ['id' => $items->get(1)->global_id]],
            ['node' => ['id' => $items->get(0)->global_id]],
        ]]]);
    });

    test('items can be filtered by their tags', function () {
        $this->rethrowGraphQLErrors();
        $user = createUser();

        /** @var \App\Models\Marker $marker */
        $marker = Marker::factory()->create();

        $mapping = createMapping($user, [
            'space_id' => $user->firstSpace()->getKey(),
            'name' => 'People',
            'type' => MappingType::PERSON,
            'fields' => [
                [
                    'id' => 'name',
                    'type' => 'SYSTEM_NAME',
                    'name' => 'Full name',
                ],
            ],
        ]);

        $mapping->addMarkerGroup($marker->group);

        /** @var \App\Models\Item $item */
        $item = createItem($mapping, ['name' => ['_v' => 'Harry']]);
        createItem($mapping, ['name' => ['_v' => 'Bill']]);

        $item->addMarker($marker);

        $this->be($user)->assertGraphQL(
            ['items' => [
                "people(filters: [{markers: [{ markerId: \"$marker->global_id\", operator: IS }]}])" => [
                    'edges' => [['node' => ['id' => $item->global_id]]],
                ],
            ]]
        );
    });

    test('items can be filtered by favorited', function () {
        $user = createUser();

        $mapping = createMapping($user, [
            'space_id' => $user->firstSpace()->getKey(),
            'name' => 'People',
            'type' => MappingType::PERSON,
            'features' => [['val' => MappingFeatureType::FAVORITES]],
        ]);

        $item1 = createItem($mapping, ['name' => ['_v' => 'Harry']]);
        $item2 = createItem($mapping, ['name' => ['_v' => 'Bill']]);
        $item1->favorite();

        $this->be($user)->assertGraphQL(
            ['items' => [
                'people(filters: [{isFavorited: true}])' => [
                    'edges' => [['node' => ['id' => $item1->global_id]]],
                ],
            ]]
        );
        $this->be($user)->assertGraphQL(
            ['items' => [
                'people(filters: [{isFavorited: false}])' => [
                    'edges' => [['node' => ['id' => $item2->global_id]]],
                ],
            ]]
        );
    });

    test('items can be filtered by priority', function () {
        $user = createUser();

        $mapping = createMapping($user, [
            'space_id' => $user->firstSpace()->getKey(),
            'name' => 'People',
            'type' => MappingType::PERSON,
            'features' => [['val' => MappingFeatureType::PRIORITIES]],
        ]);

        $item1 = createItem($mapping, ['name' => ['_v' => 'Harry']]);
        $item2 = createItem($mapping, ['name' => ['_v' => 'Bill']]);
        $item1->update(['priority' => 3]);

        $this->be($user)->assertGraphQL(
            ['items' => [
                'people(filters: [{priority: 3}])' => [
                    'edges' => [['node' => ['id' => $item1->global_id]]],
                ],
            ]]
        );
        $this->be($user)->assertGraphQL(
            ['items' => [
                'people(filters: [{priority: 0}])' => [
                    'edges' => [['node' => ['id' => $item2->global_id]]],
                ],
            ]]
        );
    });

    test('items can be filtered with advanced parameters', function () {
        $this->rethrowGraphQLErrors();
        $user = createUser();

        /** @var \App\Models\Marker $marker */
        $marker = Marker::factory()->create();

        $mapping = createMapping($user, [
            'space_id' => $user->firstSpace()->getKey(),
            'name' => 'People',
            'type' => MappingType::PERSON,
            'fields' => [
                [
                    'id' => 'name',
                    'type' => 'SYSTEM_NAME',
                    'name' => 'Full name',
                ],
                [
                    'id' => 'select',
                    'type' => 'SELECT',
                    'name' => 'Select',
                    'options' => ['valueOptions' => ['0' => 'a', '1' => 'b', '2' => 'c']],
                ],
            ],
        ]);

        $mapping->addMarkerGroup($marker->group);

        $item1 = createItem($mapping, ['name' => ['_v' => 'Harry']]);
        $item2 = createItem($mapping, ['name' => ['_v' => 'Harry']]);
        $item3 = createItem($mapping, ['name' => ['_v' => 'Bill']]);
        $item4 = createItem($mapping, ['name' => ['_v' => 'Lydia'], 'select' => ['_v' => '0']]);
        $item5 = createItem($mapping, ['name' => ['_v' => 'Sarah'], 'select' => ['_v' => '2']]);

        $item1->addMarker($marker);

        // This query should return items that match the search Harry and have the marker
        // OR items that match the search Bill or have the select field set to 2
        // Which means items 1, 3, and 4 should be returned
        $this->be($user)->assertGraphQL(
            ['items' => [
                "people(
                filters: [
                    {
                        boolean: OR,
                        filters: [
                            {boolean: AND, search: [\"Harry\"], markers: [{ markerId: \"$marker->global_id\", operator: IS }]},
                            {boolean: OR, search: [\"Bill\"], fields: [{ fieldId: \"select\", operator: IS, match: \"0\" }]},
                        ]
                    }
                ]
            )" => [
                    'edges' => [
                        ['node' => ['id' => $item4->global_id]],
                        ['node' => ['id' => $item3->global_id]],
                        ['node' => ['id' => $item1->global_id]],
                    ],
                ],
            ]]
        );
    });

    test('items can be filtered by relationship and searched', function () {
        $user = createUser();

        $parentMapping = createMapping($user, [
            'space_id' => $user->firstSpace()->getKey(),
            'name' => 'Parents',
            'type' => MappingType::PERSON,
        ]);

        $childMapping = createMapping($user, [
            'space_id' => $user->firstSpace()->getKey(),
            'name' => 'Children',
            'type' => MappingType::PERSON,
        ]);

        $relationship = $parentMapping->addRelationship(['name' => 'Children', 'type' => RelationshipType::ONE_TO_MANY, 'to' => $childMapping]);

        $parent = createItem($parentMapping, ['name' => ['_v' => 'Harry']]);
        $child1 = createItem($childMapping, ['name' => ['_v' => 'Bill']]);
        $child2 = createItem($childMapping, ['name' => ['_v' => 'Bob']]);

        $relationship->add($parent, $child1);
        $relationship->add($parent, $child2);

        $this->refreshIndex();

        $this->be($user)->assertGraphQL(
            ['items' => [
                "children(forRelation: {relationId: \"$relationship->id\", itemId: \"$parent->global_id\"}, filters: [{search: \"Bob\"}])" => [
                    'edges' => [['node' => ['id' => $child2->global_id]]],
                ],
            ]]
        );
    });

    test('filtered items can be paginated', function () {
        $this->rethrowGraphQLErrors();
        $user = createUser();

        $mapping = createMapping($user, [
            'name' => 'People',
            'type' => MappingType::PERSON,
        ]);

        $items = createItem($mapping, ['name' => ['_v' => 'Harry']], 6);

        $cursor = $this->be($user)->assertGraphQL(
            ['items' => [
                'people(first: 3, filters: [{fields: [{fieldId: "name", operator: IS, match: "\"Harry\"" }]}])' => [
                    'edges' => [
                        ['node' => ['id' => $items->get(5)->global_id]],
                        ['node' => ['id' => $items->get(4)->global_id]],
                        ['node' => ['id' => $items->get(3)->global_id]],
                    ],
                    'pageInfo' => ['endCursor' => new Ignore],
                ],
            ]]
        )->json('data.items.people.pageInfo.endCursor');

        $this->be($user)->assertGraphQL(
            ['items' => [
                'people(after: $after, first: 3, filters: [{fields: [{fieldId: "name", operator: IS, match: "\"Harry\"" }]}])' => [
                    'edges' => [
                        ['node' => ['id' => $items->get(2)->global_id]],
                        ['node' => ['id' => $items->get(1)->global_id]],
                        ['node' => ['id' => $items->get(0)->global_id]],
                    ],
                    'pageInfo' => ['endCursor' => new Ignore],
                ],
            ]],
            ['after: String' => $cursor]
        );
    });

    test('items can be sorted and paginated', function () {
        $user = createUser();
        $mapping = createMapping($user, [
            'name' => 'People',
            'type' => MappingType::PERSON,
            'fields' => [
                ['id' => 'name', 'type' => 'SYSTEM_NAME', 'name' => 'Full name'],
                ['id' => 'date', 'type' => 'DATE', 'name' => 'Date'],
            ],
        ]);

        $item1 = createItem($mapping, ['name' => ['_v' => 'Harry'], 'date' => ['_v' => '2020-01-01']]);
        $item2 = createItem($mapping, ['name' => ['_v' => 'Jill'], 'date' => ['_v' => '2019-01-04']]);
        $item3 = createItem($mapping, ['name' => ['_v' => 'Bob'], 'date' => ['_v' => '2023-12-03']]);
        $item4 = createItem($mapping, ['name' => ['_v' => 'Sarah'], 'date' => ['_v' => '2001-04-13']]);
        $item5 = createItem($mapping, ['name' => ['_v' => 'Christine'], 'date' => ['_v' => '2034-08-23']]);

        $cursor = $this->be($user)->assertGraphQL(
            ['items' => [
                'people(first: 3, orderBy: [{ field: "field:date", direction: DESC }])' => [
                    'edges' => [
                        ['node' => ['id' => $item5->global_id]],
                        ['node' => ['id' => $item3->global_id]],
                        ['node' => ['id' => $item1->global_id]],
                    ],
                    'pageInfo' => ['endCursor' => new Ignore],
                ],
            ]]
        )->json('data.items.people.pageInfo.endCursor');

        $this->be($user)->assertGraphQL(
            ['items' => [
                "people(first: 3, orderBy: [{ field: \"field:date\", direction: DESC }], after: \"$cursor\")" => [
                    'edges' => [
                        ['node' => ['id' => $item2->global_id]],
                        ['node' => ['id' => $item4->global_id]],
                    ],
                ],
            ]]
        );
    });

    test('items can be grouped by favorite and searched', function () {
        $user = createUser();

        $mapping = createMapping($user, [
            'space_id' => $user->firstSpace()->getKey(),
            'name' => 'People',
            'type' => MappingType::PERSON,
            'features' => [['val' => MappingFeatureType::FAVORITES]],
        ]);

        $item1 = createItem($mapping, ['name' => ['_v' => 'Harry']]);
        $item1->update(['favorited_at' => now()]);
        createItem($mapping, ['name' => ['_v' => 'Terry']]);
        $item3 = createItem($mapping, ['name' => ['_v' => 'Harry']]);

        $this->be($user)->assertGraphQL(
            ['groupedItems' => [
                'people(group: "FAVORITES", filters: [{search: "Harry"}])' => [
                    'groups' => [
                        ['groupHeader' => '1', 'edges' => [['node' => ['id' => $item1->global_id]]]],
                        ['groupHeader' => '0', 'edges' => [['node' => ['id' => $item3->global_id]]]],
                    ],
                ],
            ]]
        );
    });

    test('items can be grouped by select fields', function () {
        $user = createUser();

        $mapping = createMapping($user, [
            'space_id' => $user->firstSpace()->getKey(),
            'name' => 'People',
            'type' => MappingType::PERSON,
            'fields' => [
                [
                    'id' => 'name',
                    'type' => 'SYSTEM_NAME',
                    'name' => 'Full name',
                ],
                [
                    'id' => 'select',
                    'type' => 'SELECT',
                    'name' => 'Select',
                    'options' => ['valueOptions' => ['a', 'b', 'c']],
                ],
            ],
        ]);

        $item1 = createItem($mapping, ['name' => ['_v' => 'Harry'], 'select' => ['_v' => 0]]);
        $item2 = createItem($mapping, ['name' => ['_v' => 'Bill'], 'select' => ['_v' => 2]]);
        $item3 = createItem($mapping, ['name' => ['_v' => 'Sylvia'], 'select' => ['_v' => 0]]);
        $item4 = createItem($mapping, ['name' => ['_v' => 'Karen']]);

        $this->be($user)->assertGraphQL(
            ['groupedItems' => [
                'people(group: "field:select")' => [
                    'groups' => [
                        [
                            'groupHeader' => '0',
                            'edges' => [
                                ['node' => ['id' => $item3->global_id]],
                                ['node' => ['id' => $item1->global_id]],
                            ],
                        ],
                        [
                            'groupHeader' => '1',
                            'edges' => [],
                        ],
                        [
                            'groupHeader' => '2',
                            'edges' => [['node' => ['id' => $item2->global_id]]],
                        ],
                        [
                            'groupHeader' => null,
                            'edges' => [['node' => ['id' => $item4->global_id]]],
                        ],
                    ],
                ],
            ]]
        );
    });

    test('items can be grouped by boolean fields', function () {
        $user = createUser();

        $mapping = createMapping($user, [
            'space_id' => $user->firstSpace()->getKey(),
            'name' => 'People',
            'type' => MappingType::PERSON,
            'fields' => [
                [
                    'id' => 'name',
                    'type' => 'SYSTEM_NAME',
                    'name' => 'Full name',
                ],
                [
                    'id' => 'boolean',
                    'type' => 'BOOLEAN',
                    'name' => 'Boolean',
                ],
            ],
        ]);

        $item1 = createItem($mapping, ['name' => ['_v' => 'Harry'], 'boolean' => ['_v' => true]]);
        $item2 = createItem($mapping, ['name' => ['_v' => 'Bill']]);
        $item3 = createItem($mapping, ['name' => ['_v' => 'Sylvia'], 'boolean' => ['_v' => false]]);

        $this->be($user)->assertGraphQL(
            ['groupedItems' => [
                'people(group: "field:boolean")' => [
                    'groups' => [
                        [
                            'groupHeader' => '1',
                            'edges' => [['node' => ['id' => $item1->global_id]]],
                        ],
                        [
                            'groupHeader' => '0',
                            'edges' => [
                                ['node' => ['id' => $item3->global_id]],
                                ['node' => ['id' => $item2->global_id]],
                            ],
                        ],
                    ],
                ],
            ]]
        );
    });

    test('items can be grouped by category', function () {
        $user = createUser();

        $category = createCategory();
        $items = $category->items;
        $mapping = createMapping($user, [
            'space_id' => $user->firstSpace()->getKey(),
            'name' => 'People',
            'type' => MappingType::PERSON,
            'fields' => [
                [
                    'id' => 'name',
                    'type' => 'SYSTEM_NAME',
                    'name' => 'Full name',
                ],
                [
                    'id' => 'category',
                    'type' => 'CATEGORY',
                    'name' => 'Category',
                    'options' => ['category' => $category->globalId()],
                ],
            ],
        ]);

        $item1 = createItem($mapping, ['name' => ['_v' => 'Harry'], 'category' => ['_v' => $items[0]->getKey()]]);
        $item2 = createItem($mapping, ['name' => ['_v' => 'Bill'], 'category' => ['_v' => $items[1]->getKey()]]);
        $item3 = createItem($mapping, ['name' => ['_v' => 'Sylvia']]);

        $this->be($user)
            ->graphQL(
                '{
                groupedItems {
                    people(group: "field:category") {
                        groups {
                            groupHeader
                            group {
                                ...on CategoryItem {
                                    id
                                }
                            }
                            edges {
                                node {
                                    id
                                }
                            }
                        }
                    }
                }
            }'
            )
            ->assertJson(
                ['data' => ['groupedItems' => [
                    'people' => [
                        'groups' => [
                            [
                                'groupHeader' => $items[0]->global_id,
                                'group' => ['id' => $items[0]->global_id],
                                'edges' => [['node' => ['id' => $item1->global_id]]],
                            ],
                            [
                                'groupHeader' => $items[1]->global_id,
                                'group' => ['id' => $items[1]->global_id],
                                'edges' => [['node' => ['id' => $item2->global_id]]],
                            ],
                            [
                                'groupHeader' => null,
                                'group' => null,
                                'edges' => [['node' => ['id' => $item3->global_id]]],
                            ],
                        ],
                    ],
                ]]]
            );
    });

    test('items can be grouped by rating', function () {
        $user = createUser();

        $mapping = createMapping($user, [
            'space_id' => $user->firstSpace()->getKey(),
            'name' => 'People',
            'type' => MappingType::PERSON,
            'fields' => [
                [
                    'id' => 'name',
                    'type' => 'SYSTEM_NAME',
                    'name' => 'Full name',
                ],
                [
                    'id' => 'rating',
                    'type' => 'RATING',
                    'name' => 'Rating',
                ],
            ],
        ]);

        $item1 = createItem($mapping, ['name' => ['_v' => 'Harry'], 'rating' => ['_v' => 3]]);
        $item2 = createItem($mapping, ['name' => ['_v' => 'Bill'], 'rating' => ['_v' => 5]]);
        $item3 = createItem($mapping, ['name' => ['_v' => 'Sylvia']]);
        $item4 = createItem($mapping, ['name' => ['_v' => 'Sylvia'], 'rating' => ['_v' => 0]]);

        $this->be($user)->assertGraphQL(
            ['groupedItems' => [
                'people(group: "field:rating")' => [
                    'groups' => [
                        [
                            'groupHeader' => '0',
                            'edges' => [
                                ['node' => ['id' => $item4->global_id]],
                                ['node' => ['id' => $item3->global_id]],
                            ],
                        ],
                        [
                            'groupHeader' => '1',
                            'edges' => [],
                        ],
                        [
                            'groupHeader' => '2',
                            'edges' => [],
                        ],
                        [
                            'groupHeader' => '3',
                            'edges' => [['node' => ['id' => $item1->global_id]]],
                        ],
                        [
                            'groupHeader' => '4',
                            'edges' => [],
                        ],
                        [
                            'groupHeader' => '5',
                            'edges' => [['node' => ['id' => $item2->global_id]]],
                        ],
                    ],
                ],
            ]]
        );
    });

    test('items can be grouped by currency', function () {
        $user = createUser();

        $mapping = createMapping($user, [
            'space_id' => $user->firstSpace()->getKey(),
            'name' => 'People',
            'type' => MappingType::PERSON,
            'fields' => [
                [
                    'id' => 'name',
                    'type' => 'SYSTEM_NAME',
                    'name' => 'Full name',
                ],
                [
                    'id' => 'currency',
                    'type' => 'CURRENCY',
                    'name' => 'Currency',
                ],
            ],
        ]);

        $item1 = createItem($mapping, ['name' => ['_v' => 'Harry'], 'currency' => ['_v' => 'GBP']]);
        $item2 = createItem($mapping, ['name' => ['_v' => 'Bill'], 'currency' => ['_v' => 'USD']]);
        $item3 = createItem($mapping, ['name' => ['_v' => 'Sylvia']]);
        $item4 = createItem($mapping, ['name' => ['_v' => 'Sylvia'], 'currency' => ['_v' => 'USD']]);

        $this->be($user)->assertGraphQL(
            ['groupedItems' => [
                'people(group: "field:currency")' => [
                    'groups' => [
                        [
                            'groupHeader' => 'USD',
                            'edges' => [
                                ['node' => ['id' => $item4->global_id]],
                                ['node' => ['id' => $item2->global_id]],
                            ],
                        ],
                        [
                            'groupHeader' => 'GBP',
                            'edges' => [
                                ['node' => ['id' => $item1->global_id]],
                            ],
                        ],
                        [
                            'groupHeader' => null,
                            'edges' => [
                                ['node' => ['id' => $item3->global_id]],
                            ],
                        ],
                    ],
                ],
            ]]
        );
    });

    test('items can be grouped by date', function () {
        $user = createUser();

        $mapping = createMapping($user, [
            'space_id' => $user->firstSpace()->getKey(),
            'name' => 'People',
            'type' => MappingType::PERSON,
            'fields' => [
                [
                    'id' => 'name',
                    'type' => 'SYSTEM_NAME',
                    'name' => 'Full name',
                ],
                [
                    'id' => 'date',
                    'type' => 'DATE',
                    'name' => 'Date',
                ],
            ],
        ]);

        $item1 = createItem($mapping, ['name' => ['_v' => 'Harry'], 'date' => ['_v' => '2024-01-01']]);
        $item2 = createItem($mapping, ['name' => ['_v' => 'Bill'], 'date' => ['_v' => '2023-01-04']]);
        $item3 = createItem($mapping, ['name' => ['_v' => 'Sylvia']]);
        $item4 = createItem($mapping, ['name' => ['_v' => 'Sylvia'], 'date' => ['_v' => '2023-01-04']]);

        $this->be($user)->assertGraphQL(
            ['groupedItems' => [
                'people(group: "field:date")' => [
                    'groups' => [
                        [
                            'groupHeader' => '2023-01-04',
                            'edges' => [
                                ['node' => ['id' => $item4->global_id]],
                                ['node' => ['id' => $item2->global_id]],
                            ],
                        ],
                        [
                            'groupHeader' => '2024-01-01',
                            'edges' => [
                                ['node' => ['id' => $item1->global_id]],
                            ],
                        ],
                        [
                            'groupHeader' => null,
                            'edges' => [
                                ['node' => ['id' => $item3->global_id]],
                            ],
                        ],
                    ],
                ],
            ]]
        );
    });

    test('items can be grouped by priority and searched', function () {
        $user = createUser();

        $mapping = createMapping($user, [
            'space_id' => $user->firstSpace()->getKey(),
            'name' => 'People',
            'type' => MappingType::PERSON,
            'features' => [['val' => MappingFeatureType::PRIORITIES]],
        ]);

        $item1 = createItem($mapping, ['name' => ['_v' => 'Harry']]);
        $item1->update(['priority' => 5]);
        createItem($mapping, ['name' => ['_v' => 'Terry']]);
        $item3 = createItem($mapping, ['name' => ['_v' => 'Harry']]);

        $this->be($user)->assertGraphQL(
            ['groupedItems' => [
                'people(group: "PRIORITY", filters: [{search: "Harry"}])' => [
                    'groups' => [
                        ['groupHeader' => '0', 'edges' => [['node' => ['id' => $item3->global_id]]]],
                        ['groupHeader' => '1', 'edges' => []],
                        ['groupHeader' => '3', 'edges' => []],
                        ['groupHeader' => '5', 'edges' => [['node' => ['id' => $item1->global_id]]]],
                        ['groupHeader' => '9', 'edges' => []],
                    ],
                ],
            ]]
        );
    });
})->group('es');

test('a main labeled field cannot be saved without a label', function () {
    $this->withGraphQLExceptionHandling();
    $user = createUser();

    $mapping = createMapping($user, [
        'name' => 'Target companies',
        'fields' => [
            [
                'type' => 'SYSTEM_NAME',
                'name' => 'Organization',
                'options' => ['labeled' => ['freeText' => true]],
            ],
        ],
    ]);

    $this->be($user)->assertFailedGraphQLMutation(
        ['items', 'targetCompanies', 'createTargetCompany(input: $input)', 'code'],
        ['input: TargetCompanyItemCreateInput!' => [
            'data' => ['organization' => ['label' => 'test']],
        ]]
    )->assertGraphQLValidationError('input.data.organization.fieldValue', 'The "Organization" field is required.');
});
