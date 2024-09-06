<?php

declare(strict_types=1);

use App\Models\User;
use App\Models\CronResult;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('unfinished registrations are permanently removed from database', function () {
    create(User::class, [
        'created_at' => now()->subDays(31)->endOfDay(),
        'finished_registration_at' => null,
    ], 2);

    $this->artisan('registration:incomplete:purge');

    expect(User::query()->count())->toBe(0);
});

test('only unfinished registrations are removed from database', function () {
    create(User::class, [
        'created_at' => now()->subDays(31)->endOfDay(),
        'finished_registration_at' => null,
    ], 2);

    create(App\Models\User::class, [
        'created_at' => now()->subDays(31)->endOfDay(),
        'finished_registration_at' => now(),
    ]);

    $this->artisan('registration:incomplete:purge');

    expect(User::query()->count())->toBe(1);
});

test('dry run does not delete any accounts', function () {
    create(User::class, [
        'created_at' => now()->subDays(31)->endOfDay(),
        'finished_registration_at' => null,
    ], 2);

    $this->artisan('registration:incomplete:purge', ['--dry-run' => null]);

    expect(User::query()->count())->toBe(2);
});

test('count of deleted accounts is stored on cron results table', function () {
    create(User::class, [
        'created_at' => now()->subDays(31)->endOfDay(),
        'finished_registration_at' => null,
    ], 2);

    $this->artisan('registration:incomplete:purge');

    expect(CronResult::query()->count())->toBe(1)
        ->and(CronResult::query()->first()->unfinished_registrations_count)->toBe(2);
});
