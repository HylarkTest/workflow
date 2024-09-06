<?php

declare(strict_types=1);

use App\Models\Mapping;
use Illuminate\Testing\TestResponse;
use App\Core\Mappings\FieldActionFormatter;
use App\Core\Mappings\Features\MappingFeatureType;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);
beforeEach(function () {
    config(['actions.automatic' => true]);
});

test('an item history only contains the changes made', function () {
    $mapping = createItemMapping([[
        'id' => 'HEADLINE',
        'name' => 'Headline',
        'type' => 'LINE',
    ]]);

    $item = createItem($mapping, [
        'FULL_NAME' => ['_v' => 'Larry'],
        'HEADLINE' => ['_v' => 'The best around'],
    ]);

    sendUpdateItemRequest([
        'id' => $item->global_id,
        'data' => [
            'fullName' => ['fieldValue' => 'Tony'],
        ],
    ]);

    $item = $item->fresh();

    expect($item->actions)->toHaveCount(2);

    $updateAction = $item->actions->first();

    static::assertSame([
        'changes' => [
            'data' => [
                'FULL_NAME' => [
                    FieldActionFormatter::NAME => 'Full name',
                    FieldActionFormatter::TYPE => 'SYSTEM_NAME',
                    FieldActionFormatter::OPTION_MASK => 0,
                    FieldActionFormatter::VAL => ['_v' => 'Tony'],
                ],
            ],
        ],
        'original' => [
            'data' => [
                'FULL_NAME' => ['_v' => 'Larry'],
            ],
        ],
    ], $updateAction->payload);

    expect($updateAction->description(false))->toBe('Item "Tony" updated');

    static::assertSame([
        [
            'description' => 'Changed the "Full name"',
            'before' => 'Larry',
            'after' => 'Tony',
            'type' => 'line',
        ],
    ], $updateAction->changes());
});

test('creating an item shows the correct history', function () {
    $mapping = createItemMapping();

    sendCreateItemRequest([
        'data' => [
            'fullName' => ['fieldValue' => 'Larry'],
        ],
    ]);

    $item = $mapping->items->first();

    expect($item->actions)->toHaveCount(1);

    $updateAction = $item->actions->first();

    static::assertSame([
        'data' => [
            'FULL_NAME' => [
                FieldActionFormatter::NAME => 'Full name',
                FieldActionFormatter::TYPE => 'SYSTEM_NAME',
                FieldActionFormatter::OPTION_MASK => 0,
                FieldActionFormatter::VAL => ['_v' => 'Larry'],
            ],
        ],
    ], $updateAction->payload);

    static::assertSame([
        [
            'description' => 'Added the "Full name"',
            'before' => null,
            'after' => 'Larry',
            'type' => 'line',
        ],
    ], $updateAction->changes());
});

test('favoriting is recorded', function () {
    $mapping = createItemMapping([], [['val' => MappingFeatureType::FAVORITES]]);

    $item = createItem($mapping, ['FULL_NAME' => ['_v' => 'Larry']]);

    sendUpdateItemRequest([
        'id' => $item->global_id,
        'isFavorite' => true,
    ]);

    sendUpdateItemRequest([
        'id' => $item->global_id,
        'isFavorite' => false,
    ]);

    $favoritedAt = now();

    $item = $item->fresh();

    expect($item->actions)->toHaveCount(3);

    $favoritedAction = $item->actions->get(1);
    $unFavoritedAction = $item->actions->first();

    expect($favoritedAction->payload['changes']['favorited_at'])->not->toBeNull()
        ->and($favoritedAction->payload['original'])->toBeEmpty();

    expect($favoritedAction->changes())->toBe([[
        'description' => 'Favorited',
        'before' => null,
        'after' => null,
        'type' => 'line',
    ]]);

    expect($unFavoritedAction->payload['changes'])->toBeEmpty()
        ->and($unFavoritedAction->payload['original']['favorited_at'])->not->toBeNull();

    expect($unFavoritedAction->changes())->toBe([[
        'description' => 'Unfavorited',
        'before' => null,
        'after' => null,
        'type' => 'line',
    ]]);
});

test('item history can be fetched', function () {
    $mapping = createItemMapping([[
        'id' => 'HEADLINE',
        'name' => 'Headline',
        'type' => 'LINE',
    ]]);

    $item = createItem($mapping, [
        'FULL_NAME' => ['_v' => 'Larry'],
        'HEADLINE' => ['_v' => 'The best around'],
    ]);

    sendUpdateItemRequest([
        'id' => $item->global_id,
        'data' => [
            'fullName' => ['fieldValue' => 'Tony'],
        ],
    ]);

    $this->assertGraphQL(
        ["history(forNode: \"$item->global_id\")" => ['edges' => [
            ['node' => [
                'type' => 'UPDATE',
                'description(withPerformer: false)' => 'Item "Tony" updated',
                'changes' => [[
                    'description' => 'Changed the "Full name"',
                    'before' => 'Larry',
                    'after' => 'Tony',
                    'type' => 'line',
                ]],
            ]],
            ['node' => [
                'type' => 'CREATE',
                'description' => 'Item "Larry" created',
                'changes' => [
                    [
                        'description' => 'Added the "Full name"',
                        'before' => null,
                        'after' => 'Larry',
                        'type' => 'line',
                    ],
                    [
                        'description' => 'Added the "Headline"',
                        'before' => null,
                        'after' => 'The best around',
                        'type' => 'line',
                    ],
                ],
            ]],
        ]]]
    );
});

// Helpers
function createItemMapping(array $fields = [], array $features = []): Mapping
{
    $user = createUser();
    test()->be($user);

    return createMapping($user, [
        'name' => 'Photography clients',
        'fields' => [
            [
                'id' => 'FULL_NAME',
                'type' => 'SYSTEM_NAME',
                'name' => 'Full name',
            ],
            ...$fields,
        ],
        'features' => $features,
    ]);
}

function sendCreateItemRequest($body): TestResponse
{
    return test()->graphQL('
        mutation CreatePhotographyClient($input: PhotographyClientItemCreateInput!) {
            items {
                photographyClients {
                    createPhotographyClient(input: $input) {
                        code
                        photographyClient {
                            id
                        }
                    }
                }
            }
        }
        ', [
        'input' => $body,
    ])->assertSuccessfulGraphQL();
}

function sendUpdateItemRequest($body): TestResponse
{
    return test()->graphQL('
        mutation UpdatePhotographyClient($input: PhotographyClientItemUpdateInput!) {
            items {
                photographyClients {
                    updatePhotographyClient(input: $input) {
                        code
                        photographyClient {
                            id
                        }
                    }
                }
            }
        }
        ', [
        'input' => $body,
    ])->assertSuccessfulGraphQL();
}
