<?php

declare(strict_types=1);

use App\Models\Page;
use App\Core\Groups\Role;
use App\Core\Pages\PageType;
use App\Core\Mappings\Features\MappingFeatureType;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class)
    ->group('customization');

test('user can customize the navigation shortcuts', function () {
    $this->withGraphQLExceptionHandling();
    $user = createUser();

    $base = $user->bases()->first();
    $pivot = $base->pivot;

    expect($pivot->settings->shortcuts)->toBe([]);

    /** @var Page $page */
    $page = $base->spaces()->first()->pages()->create([
        'name' => 'My personal contacts',
        'type' => PageType::ENTITIES,
    ]);

    $this->be($user)->assertGraphQLMutation(
        ['updateProfile(input: $input)' => [
            'base' => ['id' => $pivot->globalId()],
            'activeBase' => ['id' => $pivot->globalId()],
        ]],
        ['input: UpdateProfileInput!' => [
            'preferences' => [
                'shortcuts' => [
                    ['id' => 'TODOS', 'type' => 'FEATURE'],
                    ['id' => 'CALENDAR', 'type' => 'FEATURE'],
                    ['id' => $page->globalId(), 'type' => 'PAGE'],
                ],
            ],
        ]]
    );

    expect($pivot->fresh()->settings->shortcuts)->toBe([
        ['id' => 'TODOS', 'type' => 'FEATURE'],
        ['id' => 'CALENDAR', 'type' => 'FEATURE'],
        ['id' => $page->globalId(), 'type' => 'PAGE'],
    ]);
});

test('user can customize which widget appear on the footer', function () {
    $this->withGraphQLExceptionHandling();
    $user = createUser();

    $base = $user->bases()->first();
    $pivot = $base->pivot;

    $this->be($user)->assertGraphQLMutation(
        ['updateProfile(input: $input)' => [
            'base' => ['id' => $pivot->globalId()],
            'activeBase' => ['id' => $pivot->globalId()],
        ]],
        ['input: UpdateProfileInput!' => [
            'preferences' => [
                'widgets' => [
                    'addShortcuts' => ['TODOS'],
                ],
            ]],
        ],
    );

    expect($pivot->fresh()->settings->widgets)->toBe([
        'addShortcuts' => [MappingFeatureType::TODOS],
    ]);
});

test('user can disable footer widget', function () {
    $this->withGraphQLExceptionHandling();
    $user = createUser();
    $base = $user->bases()->first();
    $pivot = $base->pivot;

    $this->be($user)->assertGraphQLMutation(
        ['updateProfile(input: $input)' => [
            'base' => ['id' => $pivot->globalId()],
            'activeBase' => ['id' => $pivot->globalId()],
        ]],
        ['input: UpdateProfileInput!' => [
            'preferences' => [
                'widgets' => [
                    'addShortcuts' => [],
                ],
            ],
        ]],
    );

    expect($pivot->fresh()->settings->widgets)->toBe(['addShortcuts' => []]);
});

test('user can update the theme color on their personal base', function () {
    $this->withGraphQLExceptionHandling();
    $user = createUser();
    $base = $user->bases()->first();

    $this->be($user)->assertGraphQLMutation(
        'updateBase(input: $input)',
        ['input: UpdateBaseInput!' => [
            'accentColor' => 'electricPurple',
        ]],
    );

    expect($base->settings->settings->accentColor)->toBe('electricPurple');
});

test('admin can update collaborative base theme color', function () {
    $this->withGraphQLExceptionHandling();
    $user = createCollabUser();
    $base = $user->bases->last();

    $this->be($user)->assertGraphQLMutation(
        'updateBase(input: $input)',
        ['input: UpdateBaseInput!' => [
            'accentColor' => 'goldTips',
        ]],
    );

    expect($base->settings->settings->accentColor)->toBe('goldTips');
});

test('a user cannot update the theme of a base they are a member of', function () {
    $this->withGraphQLExceptionHandling();
    $user = createCollabUser(Role::MEMBER);
    $base = $user->bases->last();

    $this->be($user)->assertFailedGraphQLMutation(
        'updateBase(input: $input)',
        ['input: UpdateBaseInput!' => [
            'accentColor' => 'goldTips',
        ]],
    )->assertGraphQLUnauthorized();
});
