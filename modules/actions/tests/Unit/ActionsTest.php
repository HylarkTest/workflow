<?php

declare(strict_types=1);

namespace Tests\Actions\Unit;

use Tests\Actions\TestCase;
use Actions\Core\ActionType;
use Tests\Actions\ModelWithAction;
use Illuminate\Foundation\Auth\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ActionsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A model can record actions
     *
     * @test
     */
    public function a_model_can_record_actions(): void
    {
        config(['actions.mandatory_performer' => false]);

        /** @var \Tests\Actions\ModelWithAction $model */
        $model = ModelWithAction::query()->forceCreate([
            'name' => 'Larry',
        ]);

        static::assertCount(1, $model->actions);
        static::assertTrue(ActionType::CREATE()->is($model->actions->first()->type));
    }

    /**
     * A model can record actions with the currently logged in user
     *
     * @test
     */
    public function a_model_can_record_actions_with_the_currently_logged_in_user(): void
    {
        $user = User::query()->forceCreate([
            'email' => 'abc@email.com',
            'password' => 'secret',
            'name' => 'Bob',
        ]);

        $this->be($user);

        /** @var \Tests\Actions\ModelWithAction $model */
        $model = ModelWithAction::query()->forceCreate([
            'name' => 'Larry',
        ]);

        $model->save();

        static::assertTrue($user->is($model->actions->first()->performer));
    }
}
