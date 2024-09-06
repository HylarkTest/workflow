<?php

declare(strict_types=1);

namespace Tests\Actions\Feature;

use Actions\Core\ActionType;
use Tests\Actions\ModelWithAction;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;
use Tests\LighthouseHelpers\InteractsWithGraphQLExceptionHandling;

class ActionRecordDirectiveTest extends FeatureTestCase
{
    use InteractsWithGraphQLExceptionHandling;
    use MakesGraphQLRequests;

    /**
     * The record directive manually records the mutation
     *
     * @test
     */
    public function the_record_directive_manually_records_the_mutation(): void
    {
        $this->withoutGraphQLExceptionHandling();
        config([
            'actions.automatic' => false,
            'actions.mandatory_performer' => false,
        ]);

        /** @var \Tests\Actions\ModelWithAction $model */
        $model = ModelWithAction::query()->forceCreate([
            'name' => 'Larry',
        ]);

        $this->graphQL("
        mutation {
            first: updateModelWithAction(id: \"$model->id\", name: \"Toby\") { id }
            second: updateModelWithAction(id: \"$model->id\", name: \"Gary\") { id }
        }
        ");

        $model->update(['name' => 'Barry']);

        $model = $model->fresh();
        static::assertSame('Barry', $model->name);
        static::assertCount(2, $model->actions);
        static::assertTrue($model->actions->first()->type->is(ActionType::UPDATE()));
        static::assertSame([
            'changes' => ['name' => 'Gary'],
            'original' => ['name' => 'Toby'],
        ], $model->actions->first()->payload);
    }
}
