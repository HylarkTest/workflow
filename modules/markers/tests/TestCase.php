<?php

declare(strict_types=1);

namespace Tests\Markers;

use Markers\MarkersServiceProvider;
use Illuminate\Foundation\Auth\User;
use Orchestra\Testbench\TestCase as BaseTestCase;
use Illuminate\Database\Eloquent\Relations\Relation;

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
            MarkersServiceProvider::class,
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();

        Relation::requireMorphMap(false);

        $this->handleValidationExceptions();

        $this->loadLaravelMigrations();

        $this->artisan('migrate');
    }
}
