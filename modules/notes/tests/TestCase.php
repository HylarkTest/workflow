<?php

declare(strict_types=1);

namespace Tests\Notes;

use Notes\NotesServiceProvider;
use Illuminate\Support\Facades\Cache;
use Orchestra\Testbench\TestCase as BaseTestCase;
use Tests\LighthouseHelpers\InteractsWithGraphQLExceptionHandling;

abstract class TestCase extends BaseTestCase
{
    use InteractsWithGraphQLExceptionHandling;

    protected static bool $firstTest = true;

    /**
     * Get package providers.
     *
     * @param  \Illuminate\Foundation\Application  $app
     */
    protected function getPackageProviders($app): array
    {
        return [
            NotesServiceProvider::class,
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->handleValidationExceptions();

        $this->loadLaravelMigrations();

        $this->artisan('migrate');

        config([
            // 'lighthouse.schema.register' => __DIR__.'/resources/schema.graphql',
            'lighthouse.debug' => 15,
            'app.debug' => true,
        ]);

        if (static::$firstTest) {
            Cache::flush();
            static::$firstTest = false;
        }
    }
}
