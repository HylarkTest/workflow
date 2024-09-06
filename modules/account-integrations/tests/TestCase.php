<?php

declare(strict_types=1);

namespace Tests\AccountIntegrations;

use Illuminate\Foundation\Auth\User;
use Orchestra\Testbench\TestCase as BaseTestCase;
use Webklex\IMAP\Providers\LaravelServiceProvider;
use AccountIntegrations\AccountIntegrationsServiceProvider;
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
            AccountIntegrationsServiceProvider::class,
            LaravelServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->handleValidationExceptions();

        $this->loadLaravelMigrations();

        $this->artisan('migrate');
    }
}
