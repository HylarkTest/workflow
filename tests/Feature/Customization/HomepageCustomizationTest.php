<?php

declare(strict_types=1);

use App\Core\Groups\Role;
use App\Core\Pages\PageType;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class)
    ->group('homepage', 'customization');

test('user can customize homepage shortcuts sizes', function () {
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
                'homepage' => [
                    'shortcuts' => [
                        'customize' => 'SMALL',
                        'integrations' => 'FULL',
                    ],
                    'spaces' => [],
                ],
            ],
        ]]
    );

    expect($pivot->fresh()->settings->homepage)->toBe([
        'shortcuts' => [
            'customize' => 'SMALL',
            'integrations' => 'FULL',
        ],
        'spaces' => [],
    ]);
});

test('user can customize what pages to display on their home page', function () {
    $this->withGraphQLExceptionHandling();
    $user = createUser();

    $base = $user->bases()->first();
    $pivot = $base->pivot;

    $page1 = $base->spaces()->first()->pages()->create([
        'name' => 'Heroes',
        'type' => PageType::ENTITIES,
    ]);

    $page2 = $base->spaces()->first()->pages()->create([
        'name' => 'Villains',
        'type' => PageType::ENTITIES,
    ]);

    $this->be($user)->assertGraphQLMutation(
        ['updateProfile(input: $input)' => [
            'base' => ['id' => $pivot->globalId()],
            'activeBase' => ['id' => $pivot->globalId()],
        ]],
        ['input: UpdateProfileInput!' => [
            'preferences' => [
                'homepage' => [
                    'shortcuts' => [
                        'customize' => 'SMALL',
                        'integrations' => 'FULL',
                    ],
                    'spaces' => [
                        $page2->space->globalId() => [
                            'pages' => [
                                $page2->globalId(),
                            ],
                        ],
                    ],
                ],
            ],
        ]]
    );

    expect($pivot->fresh()->settings->homepage)->toBe([
        'shortcuts' => [
            'customize' => 'SMALL',
            'integrations' => 'FULL',
        ],
        'spaces' => [
            $page2->space->globalId() => [
                'pages' => [
                    $page2->globalId(),
                ],
            ],
        ],
    ])->not()->toContain($page1->globalId());
});

test('admin can customize what pages to show on collaborative base home page as default for all base users', function () {
    $this->withGraphQLExceptionHandling();
    $user = createCollabUser();
    $base = $user->bases->last();
    tenancy()->initialize($base);

    $page1 = $base->spaces()->first()->pages()->create([
        'name' => 'Heroes',
        'type' => PageType::ENTITIES,
    ]);

    $page2 = $base->spaces()->first()->pages()->create([
        'name' => 'Villains',
        'type' => PageType::ENTITIES,
    ]);

    $this->be($user)->assertGraphQLMutation(
        'updateBase(input: $input)',
        ['input: UpdateBaseInput!' => [
            'homepage' => [
                'spaces' => [
                    $page1->space->globalId() => [
                        'pages' => [
                            $page1->globalId(),
                        ],
                    ],
                ],
            ],
        ]],
    );

    expect($base->settings->settings->homepage)->toBe([
        'spaces' => [
            $page1->space->globalId() => [
                'pages' => [
                    $page1->globalId(),
                ],
            ],
        ],
    ])->not()->toContain($page2->globalId());
});

test('user cannot update the default values for the homepage preferences on a base they are a member of', function () {
    $this->withGraphQLExceptionHandling();
    $user = createCollabUser(Role::MEMBER);
    $base = $user->bases->last();
    $space = $base->spaces()->create(['name' => 'Space']);

    $page = $space->pages()->create([
        'name' => 'Heroes',
        'type' => PageType::ENTITIES,
    ]);

    $this->be($user)->assertFailedGraphQLMutation(
        'updateBase(input: $input)',
        ['input: UpdateBaseInput!' => [
            'homepage' => [
                'spaces' => [
                    $space->globalId() => [
                        'pages' => [
                            $page->globalId(),
                        ],
                    ],
                ],
            ],
        ]],
    )->assertGraphQLUnauthorized();
});
