<?php

declare(strict_types=1);

use Tests\Mappings\Concerns\ChangesSchema;
use Mappings\Core\Mappings\Fields\FieldType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\LighthouseHelpers\InteractsWithGraphQLExceptionHandling;

uses(ChangesSchema::class);
uses(InteractsWithGraphQLExceptionHandling::class);
uses(RefreshDatabase::class);

test('a mapping creates a new graphql type with the id', function () {
    $this->withoutGraphQLExceptionHandling();
    $user = createUser();
    $mapping = createMapping($user, [
        'name' => 'people',
        'apiName' => 'people',
        'singularName' => 'person',
        'apiSingularName' => 'person',
        'fields' => [
            [
                'id' => 'name',
                'name' => 'Name',
                'type' => FieldType::SYSTEM_NAME()->key,
            ],
            [
                'id' => 'descriptions',
                'name' => 'Descriptions',
                'type' => FieldType::PARAGRAPH()->key,
                'options' => ['list' => ['max' => 5]],
            ],
        ],
    ]);

    $firstItem = createItem($mapping, [
        'name' => ['_v' => 'Larry'],
        'descriptions' => ['_c' => [['_v' => 'I am an item.']]],
    ]);

    $secondItem = createItem($mapping, [
        'name' => ['_v' => 'Toby'],
        'descriptions' => ['_c' => [['_v' => 'I am another item.']]],
    ]);

    $itemId = $firstItem->globalId();

    $this->be($user)->assertGraphQL(['items' => [
        'people' => ['edges' => [
            ['node' => [
                'id' => $secondItem->globalId(),
                'data' => [
                    'name' => ['fieldValue' => 'Toby'],
                    'descriptions' => ['listValue' => [['fieldValue' => 'I am another item.']]],
                ],
            ]],
            ['node' => [
                'id' => $itemId,
                'data' => [
                    'name' => ['fieldValue' => 'Larry'],
                    'descriptions' => ['listValue' => [['fieldValue' => 'I am an item.']]],
                ],
            ]],
        ]],
        "person(id: \"$itemId\")" => [
            'id' => $itemId,
            'data' => [
                'name' => ['fieldValue' => 'Larry'],
                'descriptions' => ['listValue' => [['fieldValue' => 'I am an item.']]],
            ],
        ],
    ]]);
});

test('a mapping creates a create type', function () {
    $this->withGraphQLExceptionHandling();
    $user = createUser();
    $mapping = createMapping($user, [
        'name' => 'people',
        'singularName' => 'person',
    ]);

    $this->be($user)->graphQL(
        '
            mutation CreatePerson($person: PersonItemCreateInput!){
                items {
                    people {
                        createPerson(input: $person) {
                            person {
                                id
                                data {
                                    name { fieldValue }
                                }
                            }
                        }
                    }
                }
            }
        ',
        [
            'person' => [
                'data' => ['name' => ['fieldValue' => 'Larry']],
            ],
        ])->assertJson(['data' => ['items' => ['people' => [
            'createPerson' => [
                'person' => [
                    'id' => base64_encode('Item:1'),
                    'data' => [
                        'name' => ['fieldValue' => 'Larry'],
                    ],
                ],
            ],
        ]]]], true);

    /** @var \Mappings\Models\Item $item */
    $item = $mapping->items()->first();

    expect($item->data['name'])->toBe(['_v' => 'Larry']);
});

test('a mapping creates an update type', function () {
    $user = createUser();
    $mapping = createMapping($user, [
        'name' => 'people',
        'singularName' => 'person',
    ]);

    $item = createItem($mapping, ['name' => ['_v' => 'Larry']]);
    $itemId = $item->globalId();

    $this->be($user)->graphQL('
        mutation UpdatePerson($person: PersonItemUpdateInput!){
            items {
                people {
                    updatePerson(input: $person) {
                        person {
                            id
                            data {
                                name { fieldValue }
                            }
                        }
                    }
                }
            }
        }
        ',
        [
            'person' => [
                'id' => $item->global_id,
                'data' => ['name' => ['fieldValue' => 'Toby']],
            ],
        ],
    )->assertJson(['data' => ['items' => ['people' => [
        'updatePerson' => [
            'person' => [
                'id' => $itemId,
                'data' => [
                    'name' => ['fieldValue' => 'Toby'],
                ],
            ],
        ],
    ]]]], true);

    expect($item->fresh()->data['name'])->toBe(['_v' => 'Toby']);
});

test('only items on a mapping can be fetched with the mapping key', function () {
    $this->withGraphQLExceptionHandling();
    $user = createUser();
    $firstMapping = createMapping($user, ['api_singular_name' => 'first']);
    $secondMapping = createMapping($user, ['api_singular_name' => 'second']);

    /** @var \Mappings\Models\Item $firstItem */
    $firstItem = $firstMapping->items()->create([
        'data' => ['name' => 'Larry'],
    ]);

    /** @var \Mappings\Models\Item $secondItem */
    $secondItem = $secondMapping->items()->create([
        'data' => ['name' => 'Toby'],
    ]);

    $secondItem = $secondItem->globalId();

    $this->be($user)->graphQL("
    {
        items {
            first(id: \"$secondItem\") { id }
        }
    }
    ")->assertJson([
        'errors' => [[
            'extensions' => ['category' => 'missing'],
        ]],
    ], true);
});
