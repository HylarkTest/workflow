<?php

declare(strict_types=1);

namespace Tests\Actions\Unit;

use Tests\Actions\TestCase;
use Actions\Core\ActionType;
use Tests\Actions\ModelWithAction;
use Illuminate\Database\Eloquent\Model;
use Actions\Models\Contracts\ModelActionRecorder;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ActionPayloadTest extends TestCase
{
    use RefreshDatabase;

    /**
     * The payload of a CREATE action is the attributes
     *
     * @test
     */
    public function the_payload_of_a_create_action_is_the_attributes(): void
    {
        /** @var \Tests\Actions\ModelWithAction $model */
        $model = ModelWithAction::query()->forceCreate([
            'name' => 'Larry',
        ]);

        static::assertSame(['name' => 'Larry'], $model->createAction->payload);
    }

    /**
     * The payload of an UPDATE action is the changed attributes
     *
     * @test
     */
    public function the_payload_of_an_update_action_is_the_changed_attributes(): void
    {
        /** @var \Tests\Actions\ModelWithAction $model */
        $model = ModelWithAction::query()->forceCreate([
            'name' => 'Larry',
        ]);

        $model->forceFill(['name' => 'Toby'])->save();

        static::assertSame(['changes' => ['name' => 'Toby'], 'original' => ['name' => 'Larry']], $model->updateActions->first()->payload);
    }

    /**
     * DELETE and RESTORE actions have no payload
     *
     * @test
     */
    public function delete_and_restore_actions_have_no_payload(): void
    {
        /** @var \Tests\Actions\ModelWithAction $model */
        $model = ModelWithAction::query()->forceCreate([
            'name' => 'Larry',
        ]);

        $model->delete();
        $model->restore();
        $model->forceDelete();

        foreach ($model->actions->slice(0, -1) as $action) {
            static::assertNull($action->payload);
        }
    }

    /**
     * The payload can be set manually
     *
     * @test
     */
    public function the_payload_can_be_set_manually(): void
    {
        /** @var \Tests\Actions\Unit\ModelWithPayload $model */
        $model = ModelWithPayload::query()->forceCreate([
            'name' => 'Larry',
        ]);

        static::assertSame(['test' => 'payload'], $model->createAction->payload);
    }
}

class ModelWithPayload extends ModelWithAction implements ModelActionRecorder
{
    public function getActionPayload(ActionType $type, ?Model $performer): array
    {
        return ['test' => 'payload'];
    }

    public function getActionType(?Model $performer, ?ActionType $baseType): ActionType
    {
        return $baseType;
    }
}
