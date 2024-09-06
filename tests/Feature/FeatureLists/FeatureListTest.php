<?php

declare(strict_types=1);

use App\Models\Page;
use Illuminate\Support\Str;
use App\Core\Pages\PageType;
use App\Models\Contracts\FeatureList;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

$listTypes = [
    'calendar',
    'drive',
    'linkList',
    'todoList',
    'notebook',
    'pinboard',
];

test('a user can fetch their lists including a primary list', function (string $query) {
    $user = createUser();
    $query = Str::plural($query);

    $list = createList($user, $query, [], 1);

    $this->be($user)->assertGraphQL([$query => ['edges' => [
        ['node' => [
            'name' => $list->name,
            'color' => (string) $list->colorOrDefault(),
            getChildrenQuery($list).'Count' => 1,
        ]],
        ['node' => [
            'name' => match ($query) {
                'todoLists' => 'Inbox',
                default => 'General',
            },
            'color' => mb_strtolower(config('planner.events.default_color')),
            getChildrenQuery($list).'Count' => match ($query) {
                // Some default lists get default children
                'calendars' => 1,
                'todoLists' => 2,
                default => 0,
            },
        ]],
    ]]]);
})->with($listTypes);

test('a user can create a list', function (string $query) {
    $user = createUser();

    $this->be($user);
    sendListMutation($query, 'create', [
        'spaceId' => $user->firstSpace()->global_id,
        'name' => 'New list',
    ])->assertSuccessfulGraphQL();

    expect($user->firstPersonalBase()->{Str::plural($query)})->toHaveCount(1);
})->with($listTypes);

test('a list created with a duplicate name gets incremented', function (string $query) {
    $user = createUser();

    $this->be($user);
    $base = $user->firstPersonalBase();
    createList($user, $query, ['name' => 'New list']);

    sendListMutation($query, 'create', [
        'spaceId' => $user->firstSpace()->global_id,
        'name' => 'New list',
    ])->assertSuccessfulGraphQL();

    $lists = $base->{Str::plural($query)};

    expect($lists)->toHaveCount(2)
        ->and($lists->last()->name)->toBe('New list (1)');

    sendListMutation($query, 'create', [
        'spaceId' => $user->firstSpace()->global_id,
        'name' => 'New list',
    ])->assertSuccessfulGraphQL();

    $lists = $base->fresh()->{Str::plural($query)};
    expect($lists)->toHaveCount(3)
        ->and($lists->last()->name)->toBe('New list (2)');
})->with($listTypes);

test('a list must be created with a name', function (string $query) {
    $this->withGraphQLExceptionHandling();
    $user = createUser();

    $this->be($user);
    sendListMutation($query, 'create', [
        'spaceId' => $user->firstSpace()->global_id,
        'name' => '',
    ])->assertGraphQLValidationError('input.name', 'The name field is required.');
})->with($listTypes);

test('a user can update a list', function (string $query) {
    $user = createUser();
    $list = createList($user, $query);

    $this->be($user);
    sendListMutation($query, 'update', [
        'id' => $list->globalId(),
        'name' => 'New name',
        'color' => '#FFFFFF',
    ])->assertSuccessfulGraphQL();

    $list = $list->fresh();
    expect($list)
        ->name->toBe('New name')
        ->and((string) $list->color)->toBe('#ffffff');
})->with($listTypes);

test('a user cannot update a list they do not own', function (string $query) {
    $this->withGraphQLExceptionHandling();
    $user = createUser();
    $otherUser = createUser();
    $list = createList($otherUser, $query);

    $this->be($user);
    sendListMutation($query, 'update', [
        'id' => $list->globalId(),
        'name' => 'New list',
        'color' => '#FFFFFF',
    ])->assertGraphQLMissing();

    $list = $list->fresh();
    expect($list)
        ->name->not->toBe('New list')
        ->and((string) $list->color)->not->toBe('#ffffff');
})->with($listTypes);

test('a user can delete a list', function (string $query) {
    $user = createUser();
    $relation = Str::plural($query);
    $list = createList($user, $query);

    $this->be($user);
    sendListMutation($query, 'delete', [
        'id' => $list->globalId(),
    ])->assertSuccessfulGraphQL();

    expect($user->firstPersonalBase()->$relation)->toHaveCount(0);
})->with($listTypes);

test('a user cannot delete a list they do not own', function (string $query) {
    $this->withGraphQLExceptionHandling();
    $user = createUser();
    $otherUser = createUser();
    $relation = Str::plural($query);
    $list = createList($otherUser, $query);

    $this->be($user);
    sendListMutation($query, 'delete', [
        'id' => $list->globalId(),
    ])->assertGraphQLMissing();

    expect($otherUser->firstPersonalBase()->$relation)->toHaveCount(1);
})->with($listTypes);

test('a user can restore a deleted list', function (string $query) {
    $user = createUser();
    $relation = Str::plural($query);
    $list = createList($user, $query, [], 1);
    $child = $list->children()->first();
    $list->delete();
    expect($child->fresh()->trashed())->toBeTrue();

    $this->be($user);
    sendListMutation($query, 'restore', [
        'id' => $list->globalId(),
    ])->assertSuccessfulGraphQL();

    expect($user->firstPersonalBase()->$relation)->toHaveCount(1)
        ->and($child->fresh()->trashed())->toBeFalse();
})->with($listTypes);

test('a user cannot restore a list they do not own', function (string $query) {
    $this->withGraphQLExceptionHandling();
    $user = createUser();
    $otherUser = createUser();
    $relation = Str::plural($query);
    $list = createList($otherUser, $query);
    $list->delete();

    $this->be($user);
    sendListMutation($query, 'restore', [
        'id' => $list->globalId(),
    ])->assertGraphQLMissing();

    expect($otherUser->firstPersonalBase()->$relation)->toHaveCount(0);
})->with($listTypes);

test('a list can be fully deleted', function (string $query) {
    $user = createUser();
    $list = createList($user, $query, [], 1);
    $child = $list->children()->first();
    $list->delete();
    expect($child->fresh()->trashed())->toBeTrue();

    $this->be($user);
    sendListMutation($query, 'delete', [
        'id' => $list->globalId(),
        'force' => true,
    ])->assertSuccessfulGraphQL();

    expect($list->fresh())->toBeNull()
        ->and($child->fresh())->toBeNull();
})->with($listTypes);

test('a list can be moved', function (string $query) {
    $user = createUser();
    $firstList = createList($user, $query, ['is_default' => true]);
    $secondList = createList($user, $query);
    $thirdList = createList($user, $query);

    $this->be($user);
    sendListMutation($query, 'move', [
        'id' => $thirdList->globalId(),
        'previousId' => null,
    ])->assertSuccessfulGraphQL();
    sendListMutation($query, 'move', [
        'id' => $secondList->globalId(),
        'previousId' => $thirdList->globalId(),
    ])->assertSuccessfulGraphQL();

    $this->assertGraphQL([Str::plural($query) => ['edges' => [
        ['node' => ['id' => $thirdList->globalId()]],
        ['node' => ['id' => $secondList->globalId()]],
        ['node' => ['id' => $firstList->globalId()]],
    ]]]);
})->with($listTypes);

test('deleting a list removes it from pages with that list', function (string $query) {
    $user = createUser();
    /** @var \App\Models\Space $space */
    $space = $user->firstSpace();
    $list = createList($user, $query);
    $page = $space->pages()->save(Page::factory()->make([
        'space_id' => $space->getKey(),
        'type' => match ($query) {
            'calendar' => PageType::CALENDAR,
            'todoList' => PageType::TODOS,
            'drive' => PageType::DOCUMENTS,
            'notebook' => PageType::NOTES,
            'pinboard' => PageType::PINBOARD,
            'linkList' => PageType::LINKS,
        },
        'config' => ['lists' => [$list->global_id]],
    ]));
    $list->delete();

    expect($page->fresh()->lists)->toBeEmpty();
})->with($listTypes);

test('list actions are generated', function (string $query) {
    enableAllActions();
    $user = createUser();
    $this->be($user);

    $list = createList($user, $query, [], 1);

    $this->assertGraphQL([
        "history(forNode: \"$list->global_id\")" => ['edges' => [
            ['node' => [
                'description' => Str::of($query)->snake(' ')->lower()->ucfirst()." \"$list->name\" created by $user->name",
                'changes' => [
                    [
                        'description' => 'Added the name',
                        'before' => null,
                        'after' => $list->name,
                    ],
                    [
                        'description' => 'Added the color',
                        'before' => null,
                        'after' => (string) $list->color,
                    ],
                ]],
            ],
        ]],
    ]);
})->with($listTypes);

// Helpers
function sendListMutation(string $query, string $action, array $input = [])
{
    $type = ucfirst($query);
    $ucAction = ucfirst($action);

    return test()->graphQL("
        mutation MutateList(\$input: $ucAction{$type}Input!) {
            $action$type(input: \$input) { code }
        }
    ", ['input' => $input]);
}

function getChildrenQuery(FeatureList $list): string
{
    return Str::camel($list->children()->getModel()->getTable());
}
