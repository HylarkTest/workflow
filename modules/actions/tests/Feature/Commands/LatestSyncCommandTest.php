<?php

declare(strict_types=1);

namespace Tests\Actions\Feature\Commands;

use Actions\Models\Action;
use Actions\Core\ActionType;
use Orchestra\Testbench\TestCase;
use Tests\Actions\ModelWithAction;
use Actions\ActionsServiceProvider;
use Actions\Commands\LatestSyncCommand;
use Illuminate\Database\Eloquent\Relations\Relation;

class LatestSyncCommandTest extends TestCase
{
    /**
     * Any actions that have incorrect `is_latest` value are fixed
     *
     * @test
     *
     * @group mysql
     */
    public function any_actions_that_have_incorrect_is_latest_value_are_fixed(): void
    {
        Relation::morphMap([ModelWithAction::class]);
        /** @var \Tests\Actions\ModelWithAction $firstModel */
        $firstModel = ModelWithAction::query()->forceCreate(['name' => 'Larry']);
        $firstModel->update(['name' => 'Larry2']);

        /** @var \Tests\Actions\ModelWithAction $secondModel */
        $secondModel = ModelWithAction::query()->forceCreate(['name' => 'Toby']);
        $secondModel->update(['name' => 'Toby2']);

        Action::query()->update(['is_latest' => false]);

        $this->artisan(LatestSyncCommand::class);

        static::assertTrue(ActionType::UPDATE()->is($firstModel->latestAction->type));
        static::assertTrue(ActionType::UPDATE()->is($secondModel->latestAction->type));
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set([
            'database.default' => 'mysql',
            'database.connections.mysql.host' => '192.168.56.10',
            'database.connections.mysql.database' => 'actions-test',
            'database.connections.mysql.username' => 'homestead',
            'database.connections.mysql.password' => 'secret',
        ]);
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

        $this->artisan('db:wipe');

        $this->loadLaravelMigrations();

        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');

        $this->artisan('migrate');

        config(['actions.mandatory_performer' => false]);
    }
}
