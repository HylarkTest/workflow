<?php

declare(strict_types=1);

use App\Models\Page;
use App\Models\Marker;
use App\Models\Mapping;
use App\Models\TodoList;
use App\Core\Pages\PageType;
use Illuminate\Http\UploadedFile;
use Tests\Concerns\UsesElasticsearch;
use Mappings\Core\Mappings\Fields\FieldType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\LighthouseHelpers\InteractsWithGraphQLExceptionHandling;

uses(InteractsWithGraphQLExceptionHandling::class);
uses(RefreshDatabase::class);
uses(UsesElasticsearch::class);

test('a user can create a list page', function () {
    $this->withoutGraphQLExceptionHandling();
    $user = createUser();
    $space = $user->firstSpace();
    $list = createList($user, 'linkList', ['space_id' => $space->getKey()], 1);
    $otherList = createList($user, 'linkList', ['space_id' => $space->getKey()], 1);

    $this->be($user)->assertGraphQLMutation(
        ['createListPage(input: $input)' => [
            'page' => [
                '...on ListPage' => [
                    'name' => 'New page',
                    'lists' => [$list->global_id],
                    'listItems' => ['edges' => [['node' => ['id' => $list->children->first()->global_id]]]],
                ],
            ],
        ]], ['input: CreateListPageInput!' => [
            'spaceId' => $space->global_id,
            'path' => 'New page',
            'symbol' => 'fa-user',
            'description' => 'I am a new page',
            'type' => 'LINKS',
            'lists' => [$list->global_id],
        ]])->assertSuccessfulGraphQL()->assertJson([
            'data' => [
                'createListPage' => [
                    'page' => [
                        'name' => 'New page',
                        'lists' => [$list->global_id],
                    ],
                ],
            ],
        ]);

    expect($space->pages)->toHaveCount(1);
    /** @var \App\Models\Page $page */
    $page = $space->pages->first();
    expect($page->name)->toBe('New page');
    expect($page->symbol)->toBe('fa-user');
    expect($page->description)->toBe('I am a new page');
    expect($page->type)->toBe(PageType::LINKS);
    expect($page->lists)->toHaveCount(1);
    expect($page->lists[0])->toBe($list->global_id);
});

test('a user cannot add lists of a different type', function () {
    $this->withGraphQLExceptionHandling();
    $user = createUser();
    $space = $user->firstSpace();
    $list = TodoList::factory()->create(['space_id' => $space->getKey()]);

    $this->be($user)->graphQL('
    mutation CreatePage($input: CreateListPageInput!) {
        createListPage(input: $input) {
            code
            page {
                id
            }
        }
    }
    ', ['input' => [
        'spaceId' => $space->global_id,
        'path' => 'New page',
        'symbol' => 'fa-user',
        'description' => 'I am a new page',
        'type' => 'CALENDAR',
        'lists' => [$list->global_id],
    ]])->assertGraphQLValidationError('lists', 'The lists field must be of the specified type.');
});

test('a user can create an entities page', function () {
    $this->withoutGraphQLExceptionHandling();
    $user = createUser();
    $space = $user->firstSpace();
    $mapping = Mapping::factory()->create(['space_id' => $space->getKey()]);
    $field = $mapping->fields->first();
    $marker = Marker::factory()->create();

    $this->be($user)->graphQL('
    mutation CreatePage($input: CreateEntitiesPageInput!) {
        createEntitiesPage(input: $input) {
            code
            page {
                id
                ...on EntitiesPage {
                    fieldFilters { match }
                }
            }
        }
    }
    ', ['input' => [
        'spaceId' => $space->global_id,
        'path' => 'New pages',
        'singularName' => 'New page',
        'symbol' => 'fa-user',
        'description' => 'I am a new page',
        'fieldFilters' => [[
            'fieldId' => $field->id,
            'operator' => 'IS',
            'match' => json_encode('Harry'),
        ]],
        'markerFilters' => [[
            'markerId' => $marker->global_id,
            'operator' => 'IS',
        ]],
        'mappingId' => $mapping->global_id,
    ]])->assertSuccessfulGraphQL();

    expect($space->pages)->toHaveCount(1);
    /** @var \App\Models\Page $page */
    $page = $space->pages->first();
    expect($page->name)->toBe('New pages');
    expect($page->singularName)->toBe('New page');
    expect($page->symbol)->toBe('fa-user');
    expect($page->description)->toBe('I am a new page');
    expect($page->type)->toBe(PageType::ENTITIES);
    expect($page->mapping->is($mapping))->toBeTrue();
    static::assertSame([[
        'fieldId' => $field->id,
        'operator' => 'IS',
        'match' => '"Harry"',
    ]], $page->fieldFilters);
    static::assertSame([[
        'markerId' => $marker->global_id,
        'operator' => 'IS',
    ]], $page->markerFilters);
});

test('a user can create an entity page', function () {
    $this->withoutGraphQLExceptionHandling();
    $user = createUser();
    $space = $user->firstSpace();
    $mapping = Mapping::factory()->create(['space_id' => $space->getKey()]);

    $this->be($user)->graphQL('
    mutation CreatePage($input: CreateEntityPageInput!) {
        createEntityPage(input: $input) {
            code
            page {
                id
            }
        }
    }
    ', ['input' => [
        'spaceId' => $space->global_id,
        'path' => 'New page',
        'symbol' => 'fa-user',
        'description' => 'I am a new page',
        'mappingId' => $mapping->global_id,
    ]])->assertSuccessfulGraphQL();

    expect($space->pages)->toHaveCount(1);
    /** @var \App\Models\Page $page */
    $page = $space->pages->first();
    expect($page->name)->toBe('New page');
    expect($page->symbol)->toBe('fa-user');
    expect($page->description)->toBe('I am a new page');
    expect($page->type)->toBe(PageType::ENTITY);
    expect($page->mapping->is($mapping))->toBeTrue();
});

test('a user can create a subset page', function () {
    $user = createUser();
    $space = $user->firstSpace();
    /** @var \App\Models\Mapping $mapping */
    $mapping = Mapping::factory()->create(['space_id' => $space->getKey()]);
    $field = $mapping->addField([
        'name' => 'Bool',
        'type' => FieldType::BOOLEAN(),
    ]);

    $this->be($user)->assertGraphQLMutation(
        'createEntitiesPage(input: $input)',
        ['input: CreateEntitiesPageInput!' => [
            'fieldFilters' => [[
                'fieldId' => $field->id,
                'operator' => 'IS',
                'match' => json_encode(true),
            ]],
            'mappingId' => $mapping->global_id,
            'name' => 'Subset',
            'spaceId' => $space->global_id,
            'symbol' => 'fa-user',
        ]],
    );

    expect($space->pages)->toHaveCount(1)
        ->and($space->pages->first())->fieldFilters->toHaveCount(1);
});

test('a user can create a subset page for markers', function () {
    $user = createUser();
    $space = $user->firstSpace();
    /** @var \App\Models\Mapping $mapping */
    $mapping = Mapping::factory()->create(['space_id' => $space->getKey()]);
    $markerGroup = createMarkerGroup($user, [], 1);
    $mapping->addMarkerGroup($markerGroup);

    $this->be($user)->assertGraphQLMutation(
        'createEntitiesPage(input: $input)',
        ['input: CreateEntitiesPageInput!' => [
            'markerFilters' => [[
                'markerId' => $markerGroup->markers->first()->global_id,
                'operator' => 'IS',
            ]],
            'mappingId' => $mapping->global_id,
            'name' => 'Subset',
            'spaceId' => $space->global_id,
            'symbol' => 'fa-user',
        ]],
    );

    expect($space->pages)->toHaveCount(1)
        ->and($space->pages->first())->markerFilters->toHaveCount(1);
});

test('a user can add fields and markers to the new item form', function () {
    $this->withoutGraphQLExceptionHandling();
    $user = createUser();
    $space = $user->firstSpace();
    /** @var \App\Models\Mapping $mapping */
    $mapping = Mapping::factory()->create(['space_id' => $space->getKey()]);
    $markerGroup = createMarkerGroup($user);
    $mapping->addMarkerGroup($markerGroup);

    $page = Page::query()->forceCreate([
        'name' => 'My page',
        'space_id' => $mapping->space_id,
        'type' => 'ENTITY',
        'mapping_id' => $mapping->id,
    ]);

    $this->be($user)->graphQL('
    mutation UpdatePage($input: UpdateEntityPageInput!) {
        updateEntityPage(input: $input) {
            code
            page {
                id
                ...on EntityPage {
                    newData {
                        fields
                        markers
                    }
                }
            }
        }
    }
    ', ['input' => [
        'id' => $page->global_id,
        'newData' => [
            'fields' => $mapping->fields->pluck('id')->all(),
            'markers' => $mapping->markerGroups->pluck('id')->all(),
        ],
    ]])->assertSuccessfulGraphQL()->assertJson([
        'data' => [
            'updateEntityPage' => [
                'page' => [
                    'newData' => [
                        'fields' => $mapping->fields->pluck('id')->all(),
                        'markers' => $mapping->markerGroups->pluck('id')->all(),
                    ],
                ],
            ],
        ],
    ]);

    $page->refresh();
    expect($page->config['newFields'])->toHaveCount(2);
    expect($page->config['newMarkers'])->toHaveCount(1);
});

test('removing a field removes it from the config and design', function () {
    $this->withoutGraphQLExceptionHandling();
    $user = createUser();
    $space = $user->firstSpace();
    $mapping = Mapping::factory()->create([
        'space_id' => $space->getKey(),
        'fields' => [
            [
                'id' => 'name',
                'name' => 'Name',
                'type' => FieldType::SYSTEM_NAME(),
            ],
            [
                'id' => 'field1',
                'name' => 'Field 1',
                'type' => FieldType::LINE(),
            ],
            [
                'id' => 'field2',
                'name' => 'Field 2',
                'type' => FieldType::MULTI(),
                'options' => [
                    'fields' => [
                        [
                            'id' => 'fielda',
                            'name' => 'Field A',
                            'type' => FieldType::LINE(),
                        ],
                    ],
                ],
            ],
            [
                'id' => 'field3',
                'name' => 'Field 3',
                'type' => FieldType::LINE(),
                'options' => ['list' => ['max' => 5]],
            ],
            [
                'id' => 'field4',
                'name' => 'Field 4',
                'type' => FieldType::MULTI(),
                'options' => [
                    'list' => ['max' => 5],
                    'fields' => [
                        [
                            'id' => 'fieldb',
                            'name' => 'Field B',
                            'type' => FieldType::LINE(),
                        ],
                    ],
                ],
            ],
        ],
    ]);
    $fieldToKeep = $mapping->fields->first();
    $fieldToRemove = $mapping->fields->get(1);
    /** @var \Mappings\Core\Mappings\Fields\Types\MultiField $multiField */
    $multiField = $mapping->fields->get(2);
    $listField = $mapping->fields->get(3);
    /** @var \Mappings\Core\Mappings\Fields\Types\MultiField $listMultiField */
    $listMultiField = $mapping->fields->get(4);

    $page = Page::query()->forceCreate([
        'name' => 'My page',
        'space_id' => $mapping->space_id,
        'type' => 'ENTITIES',
        'mapping_id' => $mapping->id,
        'config' => ['newFields' => $mapping->fields->pluck('id')->all()],
        'design' => [
            'views' => [[
                'id' => 'SPREADSHEET',
                'visibleData' => [
                    [
                        'dataType' => 'FIELDS',
                        'formattedId' => $fieldToRemove->id,
                    ],
                    [
                        'dataType' => 'FIELDS',
                        'formattedId' => $fieldToKeep->id,
                    ],
                    [
                        'dataType' => 'FIELDS',
                        'formattedId' => $multiField->id.'>'.$multiField->fields()->first()->id,
                    ],
                    [
                        'dataType' => 'FIELDS',
                        'formattedId' => $listField->id.'.LIST_COUNT',
                    ],
                    [
                        'dataType' => 'FIELDS',
                        'formattedId' => $listField->id.'.LIST_MAIN',
                    ],
                    [
                        'dataType' => 'FIELDS',
                        'formattedId' => $listField->id.'.LIST_FIRST',
                    ],
                    [
                        'dataType' => 'FIELDS',
                        'formattedId' => $listMultiField->id.'>'.$listMultiField->fields()->first()->id.'.LIST_COUNT',
                    ],
                    [
                        'dataType' => 'FIELDS',
                        'formattedId' => $listMultiField->id.'>'.$listMultiField->fields()->first()->id.'.LIST_MAIN',
                    ],
                    [
                        'dataType' => 'FIELDS',
                        'formattedId' => $listMultiField->id.'>'.$listMultiField->fields()->first()->id.'.LIST_FIRST',
                    ],
                ],
            ]],
            'itemDisplay' => [['fields' => [
                [
                    'dataType' => 'FIELDS',
                    'formattedId' => $fieldToRemove->id,
                ],
                [
                    'dataType' => 'FIELDS',
                    'formattedId' => $fieldToKeep->id,
                ],
                [
                    'dataType' => 'FIELDS',
                    'formattedId' => $multiField->id,
                ],
                [
                    'dataType' => 'FIELDS',
                    'formattedId' => $listField->id,
                ],
                [
                    'dataType' => 'FIELDS',
                    'formattedId' => $listMultiField->id,
                ],
            ]]],
        ],
    ]);

    $mapping->fresh()->removeField($fieldToRemove->id);

    $page->refresh();
    expect($page->config['newFields'])->toHaveCount(4);
    expect($page->design['views'][0]['visibleData'])->toHaveCount(8);
    expect($page->design['itemDisplay'][0]['fields'])->toHaveCount(4);
});

test('removing a relationship removes it from the design', function () {
    $this->withoutGraphQLExceptionHandling();
    $user = createUser();
    $space = $user->firstSpace();
    $relatedMapping = Mapping::factory()->create(['space_id' => $space]);
    /** @var \App\Models\Mapping $mapping */
    $mapping = Mapping::factory()->create(['space_id' => $space]);

    $relationshipToKeep = $mapping->addRelationship([
        'type' => 'ONE_TO_ONE',
        'to' => $relatedMapping,
        'name' => 'R1',
    ]);
    $relationshipToRemove = $mapping->addRelationship([
        'type' => 'ONE_TO_ONE',
        'to' => $relatedMapping,
        'name' => 'R2',
    ]);

    $page = Page::query()->forceCreate([
        'name' => 'My page',
        'space_id' => $mapping->space_id,
        'type' => 'ENTITIES',
        'mapping_id' => $mapping->id,
        'design' => [
            'views' => [[
                'id' => 'SPREADSHEET',
                'visibleData' => [
                    [
                        'dataType' => 'RELATIONSHIPS',
                        'formattedId' => $relationshipToRemove->id,
                    ],
                    [
                        'dataType' => 'RELATIONSHIPS',
                        'formattedId' => $relationshipToKeep->id,
                    ],
                ],
            ]],
        ],
    ]);

    $mapping->fresh()->removeRelationship($relationshipToRemove->id);

    $page->refresh();
    expect($page->design['views'][0]['visibleData'])->toHaveCount(1);
});

test('a user can only delete an entity page if there are no subset pages for the same mapping', function () {
    $this->withGraphQLExceptionHandling();
    config(['actions.automatic' => false]);
    $user = createUser();
    $space = $user->firstSpace();
    $mapping = createMapping($user);
    $page = Page::factory()->create([
        'mapping_id' => $mapping,
        'space_id' => $space,
    ]);
    $subsetPage = Page::factory()->create([
        'mapping_id' => $mapping,
        'config' => ['fieldFilters' => ['fieldId' => 'name', 'operator' => 'IS', 'match' => 'Test']],
        'space_id' => $space,
    ]);

    $this->be($user)->graphQL('
    mutation DeletePage($input: DeletePageInput!) {
        deletePage(input: $input) {
            code
        }
    }
    ', ['input' => ['id' => $page->global_id]])
        ->assertGraphQLValidationError('input.id', 'Other pages filter the full data found on this page. If you wish to delete this page, please delete the subset pages first.');

    static::assertNotNull($page->fresh());

    $this->be($user)->graphQL('
    mutation DeletePage($input: DeletePageInput!) {
        deletePage(input: $input) {
            code
        }
    }
    ', ['input' => ['id' => $subsetPage->global_id]])
        ->assertSuccessfulGraphQL();

    $this->be($user)->graphQL('
    mutation DeletePage($input: DeletePageInput!) {
        deletePage(input: $input) {
            code
        }
    }
    ', ['input' => ['id' => $page->global_id]])
        ->assertSuccessfulGraphQL();
});

test('a user can only change an entity page to a subset if there is another page showing all items for the mapping', function () {
    $this->withGraphQLExceptionHandling();
    config(['actions.automatic' => false]);
    $user = createUser();
    $space = $user->firstSpace();
    $mapping = createMapping($user);
    $page = Page::factory()->create([
        'mapping_id' => $mapping,
        'space_id' => $space,
    ]);
    $subsetPage = Page::factory()->create([
        'mapping_id' => $mapping,
        'config' => ['fieldFilters' => ['fieldId' => 'name', 'operator' => 'IS', 'match' => 'Test']],
        'space_id' => $space,
    ]);

    $this->be($user)->assertFailedGraphQLMutation(
        'updateEntitiesPage(input: $input)',
        ['input: UpdateEntitiesPageInput!' => [
            'id' => $page->global_id,
            'fieldFilters' => [['fieldId' => 'name', 'operator' => 'IS', 'match' => '"Test"']],
        ]]
    )->assertGraphQLValidationError('input.id', 'There are no other pages showing all records for this blueprint. Please create one before changing this page to a subset page.');
});

test('Deleting an entity page also deletes the mapping and items', function () {
    $user = createUser();
    $mapping = createMapping($user);
    $item = createItem($mapping);
    $page = Page::factory()->create([
        'mapping_id' => $mapping,
        'space_id' => $user->firstSpace(),
    ]);

    $this->be($user)->assertGraphQLMutation(
        'deletePage(input: $input)',
        ['input: DeletePageInput!' => [
            'id' => $page->global_id,
        ]]
    );

    expect($page->fresh())->deleted_at->not->toBeNull();
    expect($mapping->fresh())->deleted_at->not->toBeNull();
    expect($item->fresh())->deleted_at->not->toBeNull();
});

test('a user can save an image to a page', function () {
    $user = createUser();
    $base = $user->bases()->first();
    tenancy()->initialize($base);
    $mapping = createMapping($user);
    $page = $base->spaces()->first()->pages()->create([
        'name' => 'page',
        'type' => PageType::ENTITIES,
        'mapping_id' => $mapping->getKey(),
    ]);

    $file = UploadedFile::fake()->image('image.jpg');

    $this->be($user)->assertGraphQLMutation(
        'updateEntitiesPage(input: $input)',
        ['input: UpdateEntitiesPageInput!' => [
            'id' => $page->global_id,
            'image' => $file,
        ]],
    );

    expect($page->fresh()->image)->toBe('page-images/'.$file->hashName());
});

test('user can create page with an image', function () {
    $user = createUser();
    $base = $user->bases()->first();
    tenancy()->initialize($base);
    $space = $base->spaces()->first();
    $mapping = Mapping::factory()->create(['space_id' => $space->getKey()]);
    $field = $mapping->fields->first();
    $marker = Marker::factory()->create();
    $file = UploadedFile::fake()->image('image.jpg');

    $this->be($user)->assertGraphQLMutation(
        'createEntitiesPage(input: $input)',
        ['input: CreateEntitiesPageInput!' => [
            'spaceId' => $base->spaces()->first()->global_id,
            'name' => 'page',
            'symbol' => 'fa-user',
            'mappingId' => $mapping->global_id,
            'image' => $file,
        ]],
    );

    expect($base->spaces()->first()->pages()->first()->image)->toBe('page-images/'.$file->hashName());
});

test('users can fetch items through a page', function () {
    $this->withGraphQLExceptionHandling();
    config(['actions.automatic' => false]);
    $user = createUser();
    $space = $user->firstSpace();
    $mapping = createMapping($user);
    $item = createItem($mapping);
    $page = Page::factory()->create([
        'mapping_id' => $mapping,
        'space_id' => $space,
    ]);

    $this->be($user)->assertGraphQL([
        "page(id: \"$page->global_id\")" => [
            'id' => $page->global_id,
            '...on EntitiesPage' => [
                'items' => ['edges' => [[
                    'node' => ['id' => $item->global_id],
                ]]],
            ],
        ],
    ]);
});

test('users can only fetch filtered items through the page', function () {
    $this->withGraphQLExceptionHandling();
    $user = createUser();
    $space = $user->firstSpace();
    $markerGroup = createMarkerGroup($user, [], 1);
    $marker = $markerGroup->markers->first();
    $mapping = createMapping($user);
    $mappingMarkerGroup = $mapping->addMarkerGroup($markerGroup);
    $item1 = createItem($mapping, ['name' => ['_v' => 'Test']]);
    $item2 = createItem($mapping, ['name' => ['_v' => 'Test']]);
    $item3 = createItem($mapping, ['name' => ['_v' => 'Test 3']]);
    $item1->markers()->attach([$marker->id, ['context' => $mappingMarkerGroup->id]]);
    $item1->fresh()->searchable();
    $page = Page::factory()->create([
        'mapping_id' => $mapping,
        'space_id' => $space,
    ]);
    $subsetPage = Page::factory()->create([
        'mapping_id' => $mapping,
        'config' => [
            'fieldFilters' => [['fieldId' => 'name', 'operator' => 'IS', 'match' => '"Test"']],
            'markerFilters' => [['markerId' => $marker->global_id, 'operator' => 'IS', 'context' => $mappingMarkerGroup->id]],
        ],
        'space_id' => $space,
    ]);

    $this->be($user)->assertGraphQL([
        "page(id: \"$subsetPage->global_id\")" => [
            'id' => $subsetPage->global_id,
            '...on EntitiesPage' => [
                'items' => ['edges' => [[
                    'node' => ['id' => $item1->global_id],
                ]]],
            ],
        ],
    ]);
})->group('es');
