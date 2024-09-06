<?php

declare(strict_types=1);

namespace Tests\LighthouseHelpers;

use App\Models\TestItem;

class CreateOnRelationDirectiveTest extends TestCase
{
    /**
     * A model can be created on a relationship
     *
     * @test
     */
    public function a_model_can_be_created_on_a_relationship(): void
    {
        $this->setSchema(/* @lang GraphQL */ <<<'SDL'
type Query

type Mutation {
    item(id: ID! @eq): TestItemUpdate @find(model: "TestItem")
}

type TestItemUpdate {
    addChild(name: String): TestChild @createOnRelation(model: "TestChild", relation: "children")
}

type TestChild {
    id: ID!
    name: String!
}
SDL
        );

        $item = TestItem::query()->forceCreate([
            'name' => 'Larry',
        ]);

        $this->graphQL("
            mutation {
                item(id: $item->id) {
                    addChild(name: \"Toby\") {
                        name
                    }
                }
            }
        ")->assertJson(['data' => ['item' => [
            'addChild' => [
                'name' => 'Toby',
            ],
        ]]]);

        static::assertCount(1, $item->children);
        static::assertSame('Toby', $item->children->first()->name);
    }
}
