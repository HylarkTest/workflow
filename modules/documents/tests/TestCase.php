<?php

declare(strict_types=1);

namespace Tests\Documents;

use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Cache;
use Documents\DocumentsServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;
use Tests\LighthouseHelpers\InteractsWithGraphQLExceptionHandling;

abstract class TestCase extends BaseTestCase
{
    use InteractsWithGraphQLExceptionHandling;

    protected static bool $firstTest = true;

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
            DocumentsServiceProvider::class,
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->handleValidationExceptions();
        $this->handleGraphQLValidationExceptions();

        $this->loadLaravelMigrations();

        $this->withFactories(__DIR__.'/../database/factories');

        $this->artisan('migrate');

        config([
            'lighthouse.namespaces.models' => ['Documents\\Models'],
        ]);

        if (static::$firstTest) {
            Cache::flush();
            static::$firstTest = false;
        }
    }
}
