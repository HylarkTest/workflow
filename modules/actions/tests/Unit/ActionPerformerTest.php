<?php

declare(strict_types=1);

namespace Tests\Actions\Unit;

use Actions\Models\Action;
use Tests\Actions\TestCase;
use Tests\Actions\ModelWithAction;
use Illuminate\Foundation\Auth\User;
use Tests\Actions\ModelWithoutAction;
use Actions\Core\Contracts\ActionRecorder;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ActionPerformerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Actions are set with the authenticated user as the performer
     *
     * @test
     */
    public function actions_are_set_with_the_authenticated_user_as_the_performer(): void
    {
        $user = User::query()->forceCreate([
            'name' => 'Larry',
            'email' => 'l@r.ry',
            'password' => 'secret',
        ]);

        $this->be($user);

        /** @var \Tests\Actions\ModelWithAction $model */
        $model = ModelWithAction::query()->forceCreate([
            'name' => 'Toby',
        ]);

        static::assertTrue($model->createAction->performer->is($user));
    }

    /**
     * Actions are not recorded if there is no performer
     *
     * @test
     */
    public function actions_are_not_recorded_if_there_is_no_performer(): void
    {
        config(['actions.mandatory_performer' => true]);

        /** @var \Tests\Actions\ModelWithAction $model */
        $model = ModelWithAction::query()->forceCreate([
            'name' => 'Toby',
        ]);

        static::assertFalse($model->actions()->exists());
    }

    /**
     * Performers can be resolved with a custom function
     *
     * @test
     */
    public function performers_can_be_resolved_with_a_custom_function(): void
    {
        $user = User::query()->forceCreate([
            'name' => 'Larry',
            'email' => 'l@r.ry',
            'password' => 'secret',
        ]);

        $this->app[ActionRecorder::class]->setUserResolver(fn () => $user);

        /** @var \Tests\Actions\ModelWithAction $model */
        $model = ModelWithAction::query()->forceCreate([
            'name' => 'Toby',
        ]);

        static::assertTrue($model->createAction->performer->is($user));
    }

    /**
     * The performer can be set manually
     *
     * @test
     */
    public function the_performer_can_be_set_manually(): void
    {
        $user = User::query()->forceCreate([
            'name' => 'Larry',
            'email' => 'l@r.ry',
            'password' => 'secret',
        ]);

        /** @var \Tests\Actions\ModelWithoutAction $model */
        $model = ModelWithoutAction::query()->forceCreate([
            'name' => 'Toby',
        ]);

        $this->app[ActionRecorder::class]->record($model, $user);

        static::assertTrue(Action::query()->first()->performer->is($user));
    }
}
