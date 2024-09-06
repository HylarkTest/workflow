<?php

declare(strict_types=1);

namespace Tests\Actions;

use Actions\Models\Action;
use Actions\ActionsServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;
use Illuminate\Database\Eloquent\Relations\Relation;

class TestCase extends BaseTestCase
{
    public function assertSubjectName(?string $expected, Action $action): void
    {
        static::assertSame($expected, Action::getActionTranslator()->subjectNameFromAction($action));
    }

    public function assertPerformerName(?string $expected, Action $action): void
    {
        static::assertSame($expected, Action::getActionTranslator()->performerNameFromAction($action));
    }

    /**
     * Get package providers.
     *
     * @param  \Illuminate\Foundation\Application  $app
     */
    protected function getPackageProviders($app): array
    {
        return [
            ActionsServiceProvider::class,
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();

        Relation::requireMorphMap(false);

        $this->loadLaravelMigrations();

        $this->loadMigrationsFrom(__DIR__.'/database/migrations');

        $this->artisan('migrate');

        config(['actions.mandatory_performer' => false]);
    }
}
