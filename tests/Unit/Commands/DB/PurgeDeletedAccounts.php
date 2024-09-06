<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Console\Commands\DB\DeletedAccountsPurgeCommand;

uses(RefreshDatabase::class);

test('soft deleted accounts are removed after 30 days', function () {
    $user = User::factory()->create(['deleted_at' => now()->subDays(31)]);

    $this->artisan(DeletedAccountsPurgeCommand::class)
        ->assertExitCode(0);

    expect($user->fresh())->toBeNull();
});

test('only soft deleted accounts older than 30 days are deleted', function () {
    User::factory()->create();
    User::factory()->create(['deleted_at' => now()->subDays(29)]);
    $user = User::factory()->create(['deleted_at' => now()->subDays(31)]);

    $this->artisan(DeletedAccountsPurgeCommand::class)
        ->assertExitCode(0);

    expect($user->fresh())->toBeNull()
        ->and(User::withTrashed()->count())->toEqual(2);
});
