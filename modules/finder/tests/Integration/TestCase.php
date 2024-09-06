<?php

declare(strict_types=1);

namespace Tests\Finder\Integration;

use Tests\Finder\App\Client;
use Tests\Finder\App\Project;
use Finder\FinderServiceProvider;
use Laravel\Scout\ScoutServiceProvider;
use Elastic\Adapter\Indices\IndexManager;
use Orchestra\Testbench\TestCase as TestbenchTestCase;
use Elastic\Client\ServiceProvider as ElasticClientServiceProvider;
use Elastic\Migrations\ServiceProvider as ElasticMigrationsServiceProvider;

class TestCase extends TestbenchTestCase
{
    protected function getPackageProviders($app)
    {
        return [
            ScoutServiceProvider::class,
            ElasticClientServiceProvider::class,
            ElasticMigrationsServiceProvider::class,
            FinderServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('finder.driver', 'elastic');
        $app['config']->set('finder.prefix', 'test-');
        $app['config']->set('finder.models', ['finder' => [Client::class, Project::class]]);
        $app['config']->set('elastic.migrations.storage.default_path', \dirname(__DIR__).'/../elastic/migrations');
        $app['config']->set('elastic.scout_driver.refresh_documents', true);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(\dirname(__DIR__).'/App/database/migrations');

        $this->artisan('migrate')->run();
        resolve(IndexManager::class)->drop('test-*');
        $this->artisan('elastic:migrate', ['--force' => true])->run();
    }

    protected function tearDown(): void
    {
        $this->artisan('migrate:reset')->run();

        parent::tearDown();
    }
}
