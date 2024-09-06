<?php

declare(strict_types=1);

use App\Models\Item;
use App\Models\MarkerGroup;
use Tests\Concerns\UsesElasticsearch;
use Mappings\Core\Mappings\MappingType;
use Elastic\ScoutDriverPlus\Support\Query;
use App\Core\Mappings\Features\MappingFeatureType;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);
uses(UsesElasticsearch::class);

test('a user can see a single mapping', function () {
    $user = createUser();

    $mapping = createMapping($user);

    $id = $mapping->globalId();

    $this->be($user)->graphQL("
    {
        mapping(id: \"$id\") { id }
    }
    ")->assertJson([
        'data' => [
            'mapping' => ['id' => $id],
        ],
    ], true);
});

test('a user can see their mappings', function () {
    $user = createUser();

    $firstMapping = createMapping($user);

    $secondMapping = createMapping($user);

    $this->be($user)->assertGraphQL([
        'mappings' => [
            'edges' => [
                ['node' => [
                    'id' => $firstMapping->globalId(),
                ]],
                ['node' => [
                    'id' => $secondMapping->globalId(),
                ]],
            ],
        ],
    ]);
});

test('a user can filter mappings by name', function () {
    $user = createUser();

    $firstMapping = createMapping($user, ['name' => 'Jedi Masters']);

    createMapping($user, ['name' => 'Clones']);

    $this->be($user)->graphQL('
    {
        mappings(name: "Jedi") {
            edges {
                node { id }
            }
        }
    }
    ')->assertJson([
        'data' => [
            'mappings' => [
                'edges' => [
                    ['node' => ['id' => $firstMapping->globalId()]],
                ],
            ],
        ],
    ], true);
});

test('a user can filter mappings by type', function () {
    $user = createUser();

    createMapping($user, [
        'type' => MappingType::PERSON,
    ]);

    $secondMapping = createMapping($user, [
        'type' => MappingType::ITEM,
    ]);

    $this->be($user)->graphQL('
    {
        mappings(type: ITEM) {
            edges {
                node { id }
            }
        }
    }
    ')->assertJson([
        'data' => [
            'mappings' => [
                'edges' => [
                    ['node' => ['id' => $secondMapping->globalId()]],
                ],
            ],
        ],
    ], true);
});

test('a user can filter mappings by creator', function () {
    static::markTestSkipped('Skipped until teams are working');
    $user = createUser();
    $associate = createUser();

    createMapping($user)->recordAction($associate);

    /** @var \App\Models\Mapping $secondMapping */
    $secondMapping = tap(createMapping($user))->recordAction($user);

    $id = $user->globalId();

    $this->be($user)->graphQL("
    {
        mappings(createdBy: \"$id\") {
            edges {
                node { id }
            }
        }
    }
    ")->assertJson([
        'data' => [
            'mappings' => [
                'edges' => [
                    ['node' => ['id' => $secondMapping->globalId()]],
                ],
            ],
        ],
    ], true);
});

test('a user can filter mappings by last updated by', function () {
    static::markTestSkipped('Skipped until teams are working');
    $user = createUser();
    $associate = createUser();

    /** @var \App\Models\Mapping $firstMapping */
    $firstMapping = tap(createMapping($user))->recordAction($associate, true);
    /** @var \App\Models\Mapping $secondMapping */
    $secondMapping = tap(createMapping($user))->recordAction($user, true);

    tap($firstMapping)->update(['name' => 'New name'])->recordAction($user, true);
    tap($secondMapping)->update(['name' => 'New name 2'])->recordAction($associate, true);

    $id = $user->globalId();

    $this->be($user)->graphQL("
    {
        mappings(lastUpdatedBy: \"$id\") {
            edges {
                node { id }
            }
        }
    }
    ")->assertJson([
        'data' => [
            'mappings' => [
                'edges' => [
                    ['node' => ['id' => $firstMapping->globalId()]],
                ],
            ],
        ],
    ], true);
});

test('a user can change the marker groups on a mapping', function () {
    $user = createUser();

    $mapping = createMapping($user);
    $group = MarkerGroup::factory()->create();

    $this->be($user)->graphQL('
    mutation UpdateMapping($input: MappingUpdateInput!){
        updateMapping(input: $input) { code }
    }
    ', [
        'input' => [
            'id' => $mapping->global_id,
            'markerGroups' => [['group' => $group->global_id]],
        ],
    ])->assertSuccessfulGraphQL();

    $mapping->refresh();
    expect($mapping->markerGroups)->toHaveCount(1);
});

test('updating features on a mapping updates the item index', function () {
    $user = createUser();

    $mapping = createMapping($user);
    $item = createItem($mapping);

    $items = Item::searchQuery(
        Query::bool()
            ->must(Query::term()->field('mapping_id')->value($mapping->id))
            ->must(Query::term()->field('features')->value(MappingFeatureType::NOTES->value))
    )->execute()->models();

    expect($items)->toBeEmpty();

    $mapping->enableFeature(MappingFeatureType::NOTES);
    $this->refreshIndex();

    $items = Item::searchQuery(
        Query::bool()
            ->must(Query::term()->field('mapping_id')->value($mapping->id))
            ->must(Query::term()->field('features')->value(MappingFeatureType::NOTES->value))
    )->execute()->models();

    expect($items)->toHaveCount(1)
        ->and($items->first()->id)->toBe($item->id);
})->group('es');

test('Deleting a mapping also deletes the items', function () {
    $user = createUser();

    $mapping = createMapping($user);
    $item = createItem($mapping);

    $this->be($user)->assertGraphQLMutation('deleteMapping(input: $input)', [
        'input: MappingDeleteInput' => [
            'id' => $mapping->globalId(),
        ],
    ]);

    expect($item->fresh())->deleted_at->not->toBeNull();
});
