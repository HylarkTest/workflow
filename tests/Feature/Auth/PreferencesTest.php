<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('a user can fetch their default preferences', function () {
    $user = createUser();
    $this->be($user)->get('/preferences')
        ->assertSuccessful()
        ->assertJson([
            'colorMode' => 'LIGHT',
            'weekdayStart' => 0,
            'timezone' => null,
            'timeFormat' => '12',
            'dateFormat' => 'DMY',
            'moneyFormat' => [
                'separator' => ',',
                'decimal' => '.',
            ],
            'activeAppNotifications' => [
                'TIPS',
                'NEW_FEATURES',
            ],
            'lastSeenNotifications' => null,
        ]);
});

test('a user can update their preferences', function () {
    $user = createUser();

    $this->be($user)->post('/preferences', [
        'colorMode' => 'DARK',
        'weekdayStart' => 3,
        'timezone' => 'Europe/London',
        'timeFormat' => '24',
        'dateFormat' => 'YMD',
        'moneyFormat' => [
            'separator' => '.',
            'decimal' => ',',
        ],
        'activeAppNotifications' => [
            'TIPS',
        ],
        'lastSeenNotifications' => today()->toJSON(),
    ])->assertSuccessful();

    expect($user->fresh()->settings->settings->toArray())
        ->toBe([
            'colorMode' => 'DARK',
            'weekdayStart' => 3,
            'timezone' => 'Europe/London',
            'timeFormat' => '24',
            'dateFormat' => 'YMD',
            'moneyFormat' => [
                'decimal' => ',',
                'separator' => '.',
            ],
            'activeAppNotifications' => [
                'TIPS',
            ],
            'lastSeenNotifications' => today()->toJSON(),
        ]);
});

test('default preferences are not saved to the db', function () {
    $this->withoutExceptionHandling();
    $user = createUser();

    $this->be($user)->post('/preferences', [
        'colorMode' => 'DARK',
        'timeFormat' => '12',
        'moneyFormat' => [
            'separator' => ',',
            'decimal' => '.',
        ],
    ])->assertSuccessful();

    $user = $user->fresh();
    expect($user->settings->settings->toArray())
        ->toHaveKey('colorMode', 'DARK')
        ->toHaveKey('timeFormat', '12')
        ->and($user->settings->getAttributes()['settings'])->json()
        ->toBe(['colorMode' => 'DARK']);
});

test('all money formats are valid', function ($separator) {
    $user = createUser();

    $this->be($user)->post('/preferences', [
        'moneyFormat' => [
            'separator' => $separator,
            'decimal' => '.',
        ],
    ])->assertSuccessful();

    expect($user->fresh()->settings->settings->toArray())
        ->toHaveKey('moneyFormat', [
            'separator' => $separator,
            'decimal' => '.',
        ]);
})->with(['.', ',', ' ', '_', '']);
