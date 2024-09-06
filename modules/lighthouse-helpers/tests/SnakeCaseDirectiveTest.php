<?php

declare(strict_types=1);

namespace Tests\LighthouseHelpers;

use App\Models\TestItem;
use GraphQL\Executor\Executor;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;

class SnakeCaseDirectiveTest extends TestCase
{
    use MakesGraphQLRequests;

    /**
     * An argument can be converted to snake case
     *
     * @test
     */
    public function an_argument_can_be_converted_to_snake_case(): void
    {
        $this->setSchema(/* @lang GraphQL */ <<<'SDL'
type Query {
    items(name: String @eq @snakeCase): [TestItem!] @all(model: "TestItem")
}

type TestItem {
    name: String!
}
SDL
        );

        TestItem::query()->forceCreate([
            'name' => 'hello_there',
        ]);

        $this->graphQL('
            {
                items(name: "Hello there") {
                    name
                }
            }
        ')->assertJsonCount(1, 'data.items');
    }

    /**
     * A nested key can be converted to snake case
     *
     * @test
     */
    public function a_nested_key_can_be_converted_to_snake_case(): void
    {
        $this->setSchema(/* @lang GraphQL */ <<<'SDL'
type Query {
    items(orderBy: [OrderByClause!] @safeOrderBy @snakeCase(key: "column")): [TestItem!] @all(model: "TestItem")
}

type TestItem {
    name: String!
}
SDL
        );

        TestItem::query()->forceCreate([
            'name' => 'hello_there',
        ]);

        $this->graphQL('
            {
                items(orderBy: [{field: "name", direction: DESC}]) {
                    name
                }
            }
        ')->assertJsonCount(1, 'data.items');
    }

    /**
     * Fields are converted to snake case
     *
     * @test
     */
    public function fields_are_converted_to_snake_case(): void
    {
        Executor::setDefaultFieldResolver([Executor::class, 'defaultFieldResolver']);
        $this->setSchema(/* @lang GraphQL */ <<<'SDL'
type Query {
    items: [TestItem!] @all(model: "TestItem")
}

type TestItem {
    name: String!
    createdAt: String! @snakeCase
}
SDL
        );

        $randomDate = '2016-09-15 14:32:31';
        TestItem::query()->forceCreate([
            'name' => 'hello_there',
            'created_at' => $randomDate,
        ]);

        $this->graphQL('
            {
                items {
                    name
                    createdAt
                }
            }
        ')->assertJson(['data' => ['items' => [['createdAt' => $randomDate]]]]);
    }
}
