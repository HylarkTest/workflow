<?php

declare(strict_types=1);

namespace Tests\Actions\Unit;

use BenSampo\Enum\Enum;
use Tests\Actions\TestCase;
use Actions\Core\ActionType;
use Tests\Actions\ModelWithAction;
use Illuminate\Database\Eloquent\Model;
use Actions\Models\Contracts\ModelActionRecorder;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ActionTypeTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Actions are recorded throughout the lifecycle of a model
     *
     * @test
     */
    public function actions_are_recorded_throughout_the_lifecycle_of_a_model(): void
    {
        /** @var \Tests\Actions\ModelWithAction $model */
        $model = ModelWithAction::query()->forceCreate([
            'name' => 'Larry',
        ]);

        $model->save();

        static::assertNotNull($model->createAction);

        $model->forceFill(['name' => 'Toby'])->save();

        static::assertCount(1, $model->updateActions);

        $model->delete();

        static::assertCount(1, $model->deleteActions);

        $model->restore();

        static::assertCount(1, $model->restoreActions);

        $model->forceDelete();

        static::assertCount(2, $model->deleteActions()->getResults());
    }

    /**
     * Manual records can be made
     *
     * @test
     */
    public function manual_records_can_be_made(): void
    {
        config(['actions.automatic' => false]);

        /** @var \Tests\Actions\ModelWithAction $model */
        $model = ModelWithAction::query()->forceCreate([
            'name' => 'Larry',
        ]);

        $model->save();

        static::assertNull($model->createAction);
        $model->recordAction();
        static::assertNotNull($model->createAction()->getResults());

        $model = $model->fresh();

        $model->forceFill(['name' => 'Toby'])->save();

        static::assertEmpty($model->updateActions);
        $model->recordAction();
        static::assertCount(1, $model->updateActions()->getResults());

        $model->delete();

        static::assertEmpty($model->deleteActions);
        $model->recordAction();
        static::assertCount(1, $model->deleteActions()->getResults());

        $model = $model->fresh();

        $model->restore();

        static::assertEmpty($model->restoreActions);
        $model->recordAction();
        static::assertCount(1, $model->restoreActions()->getResults());

        $model = $model->fresh();

        $model->forceDelete();

        $model->recordAction();
        static::assertCount(2, $model->deleteActions()->getResults());
    }

    /**
     * New types can be used
     *
     * @test
     */
    public function new_types_can_be_used(): void
    {
        ActionType::mergeEnum(TestActionType::class);
        /** @var \Tests\Actions\Unit\ModelWithPublishedType $model */
        $model = ModelWithPublishedType::query()->forceCreate([
            'name' => 'Larry',
        ]);

        $model->save();

        static::assertCount(1, $model->actionsOfType(ActionType::PUBLISHED())->getResults());
    }
}

/**
 * @method static TestActionType PUBLISHED
 */
class TestActionType extends Enum
{
    protected const PUBLISHED = 'PUBLISHED';
}

class ModelWithPublishedType extends ModelWithAction implements ModelActionRecorder
{
    public function getActionType(?Model $performer, ?ActionType $baseType): ActionType
    {
        return ActionType::PUBLISHED();
    }

    public function getActionPayload(ActionType $type, ?Model $performer): array
    {
        return [];
    }
}
