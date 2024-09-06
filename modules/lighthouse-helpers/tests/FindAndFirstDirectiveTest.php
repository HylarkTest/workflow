<?php

declare(strict_types=1);

namespace Tests\LighthouseHelpers;

use App\Models\TestItem;

class FindAndFirstDirectiveTest extends TestCase
{
    use InteractsWithGraphQLExceptionHandling;

    /**
     * A model can be found with their global id
     *
     * @test
     */
    public function a_model_can_be_found_with_their_global_id(): void
    {
        $this->setSchema(/* @lang GraphQL */ <<<'SDL'
interface ItemInterface {
    id: ID!
    name: String!
}

type Query {
    findItemInterface(id: ID! @globalId): ItemInterface  @find
    firstItemInterface(id: ID! @globalId): ItemInterface  @first
    findItem(id: ID! @globalId(decode: "ID")): TestItem  @find
    firstItem(id: ID! @globalId(decode: "ID")): TestItem  @first
}

type TestItem implements ItemInterface {
    id: ID! @globalId
    name: String!
}

type TestChild {
    id: ID! @globalId
    name: String
}
SDL
        );

        $item = TestItem::query()->forceCreate([
            'name' => 'hello_there',
        ]);

        $this->graphQL("
            {
                findItemInterface(id: \"$item->global_id\") { id }
                firstItemInterface(id: \"$item->global_id\") { id }
                findItem(id: \"$item->global_id\") { id }
                firstItem(id: \"$item->global_id\") { id }
            }
        ")->assertJson(['data' => [
            'findItemInterface' => ['id' => $item->global_id],
            'firstItemInterface' => ['id' => $item->global_id],
            'findItem' => ['id' => $item->global_id],
            'firstItem' => ['id' => $item->global_id],
        ]]);
    }
}
