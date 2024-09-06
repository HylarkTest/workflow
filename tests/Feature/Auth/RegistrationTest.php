<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('a user can register an account', function () {
    $this->postJson('/register', [
        'name' => 'Jeff Goldblum',
        'email' => 'jg@actors.com',
        'password' => '!123aBcd',
        'permission' => true,
    ]);

    /** @var \App\Models\User $user */
    $user = User::query()->latest()->first();

    expect($user)
        ->not->toBeNull()
        ->name->toBe('Jeff Goldblum')
        ->email->toBe('jg@actors.com');

    $this->assertAuthenticatedAs($user);
});

test('registration does not create a suspicious login notification', function () {
    $this->postJson('/register', [
        'name' => 'Jeff Goldblum',
        'email' => 'jg@actors.com',
        'password' => '!123aBcd',
        'permission' => true,
    ]);

    /** @var \App\Models\User $user */
    $user = User::query()->latest()->first();

    expect($user->notifications)->toBeEmpty();
});
