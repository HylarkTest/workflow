<?php

declare(strict_types=1);

namespace Tests\Actions\Unit;

use Actions\Models\Action;
use Tests\Actions\TestCase;
use Tests\Actions\ModelWithAction;
use Tests\Actions\ModelWithoutAction;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ActionConfigurationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Models can be watched in the configuration file
     *
     * @test
     */
    public function models_can_be_watched_in_the_configuration_file(): void
    {
        $model = ModelWithoutAction::query()->forceCreate([
            'name' => 'Larry',
        ]);

        static::assertTrue(Action::query()->first()->subject->is($model));
    }

    /**
     * When a model is deleted the actions can be deleted along with it
     *
     * @test
     */
    public function when_a_model_is_deleted_the_actions_can_be_deleted_along_with_it(): void
    {
        config(['actions.cascade' => true]);

        /** @var \Tests\Actions\ModelWithAction $model */
        $model = ModelWithAction::query()->forceCreate([
            'name' => 'Larry',
        ]);

        $model->delete();

        static::assertTrue($model->actions()->exists());

        $model->forceDelete();

        static::assertFalse($model->actions()->exists());
    }

    /**
     * The action class can be extended
     *
     * @test
     */
    public function the_action_class_can_be_extended(): void
    {
        config(['actions.model' => TestAction::class]);

        /** @var \Tests\Actions\ModelWithAction $model */
        $model = ModelWithAction::query()->forceCreate([
            'name' => 'Larry',
        ]);

        static::assertTrue($model->createAction instanceof TestAction);
    }

    /**
     * Columns can be ignored in the payload
     *
     * @test
     */
    public function columns_can_be_ignored_in_the_payload(): void
    {
        config(['actions.ignore' => ['id', 'name', 'updated_at', 'created_at', 'deleted_at']]);

        /** @var \Tests\Actions\ModelWithAction $model */
        $model = ModelWithAction::query()->forceCreate([
            'name' => 'Larry',
        ]);

        static::assertSame([], $model->createAction->payload);
    }

    /**
     * @param  \Illuminate\Foundation\Application  $app
     */
    protected function getEnvironmentSetUp($app)
    {
        config(['actions.watch' => [ModelWithoutAction::class]]);
    }
}

class TestAction extends Action {}
