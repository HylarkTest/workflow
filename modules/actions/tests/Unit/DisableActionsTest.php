<?php

declare(strict_types=1);

namespace Tests\Actions\Unit;

use Tests\Actions\TestCase;
use Tests\Actions\ModelWithAction;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DisableActionsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Recording actions can be disabled
     *
     * @test
     */
    public function recording_actions_can_be_disabled(): void
    {
        config(['actions.mandatory_performer' => false]);

        ModelWithAction::withoutActions(function () {
            /** @var \Tests\Actions\ModelWithAction $model */
            $model = ModelWithAction::query()->forceCreate([
                'name' => 'Larry',
            ]);

            $model->recordAction();

            $this->assertEmpty($model->actions);

            $model->recordAction(null, true);

            $this->assertCount(1, $model->fresh()->actions);
        });
    }
}
