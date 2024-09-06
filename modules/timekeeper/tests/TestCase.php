<?php

declare(strict_types=1);

namespace Tests\Timekeeper;

use Illuminate\Foundation\Auth\User;
use Timekeeper\TimekeeperServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    public function createUser($attributes = []): User
    {
        /** @var \Illuminate\Foundation\Auth\User $user */
        $user = User::query()->forceCreate($attributes ?: [
            'name' => 'Larry',
            'email' => 'l@r.ry',
            'password' => 'secret',
        ]);

        return $user;
    }

    /**
     * Get package providers.
     *
     * @param  \Illuminate\Foundation\Application  $app
     */
    protected function getPackageProviders($app): array
    {
        return [
            TimekeeperServiceProvider::class,
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->handleValidationExceptions();

        $this->loadLaravelMigrations();

        $this->artisan('migrate');
    }
}
