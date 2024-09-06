<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('a user can delete their account', function () {
    $user = createUser();

    auth()->login($user);

    $this->be($user, 'web')
        ->delete(route('delete-account'), [], ['origin' => 'http://localhost'])
        ->assertSuccessful();

    expect($user->fresh())->deleted_at->not->toBeNull();
    expect(auth()->check())->toBeFalse();
});

test('a user can delete their account from the api', function () {
    $user = createUser();

    $this->be($user, 'api')
        ->delete(route('delete-account'))
        ->assertSuccessful();

    expect($user->fresh())->deleted_at->not->toBeNull();
});
