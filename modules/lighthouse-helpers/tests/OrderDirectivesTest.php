<?php

declare(strict_types=1);

namespace Tests\LighthouseHelpers;

use App\Models\TestItem;
use Illuminate\Database\Eloquent\Builder;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;

class OrderDirectivesTest extends TestCase
{
    use MakesGraphQLRequests;

    /**
     * A field can be made orderable
     *
     * @test
     */
    public function a_field_can_be_made_orderable(): void
    {
        $this->setSchema(/* @lang GraphQL */ <<<'SDL'
type Query {
    items: [TestItem!] @all(model: "TestItem") @orderable
}

type TestItem {
    name: String!
}
SDL
        );

        TestItem::query()->forceCreate([
            'name' => 'aaa',
        ]);
        TestItem::query()->forceCreate([
            'name' => 'zzz',
        ]);

        $this->graphQL('
            {
                items(orderBy: [{field: "name", direction: DESC}]) {
                    name
                }
            }
        ')->assertJson(['data' => ['items' => [
            ['name' => 'zzz'],
            ['name' => 'aaa'],
        ]]]);
    }

    /**
     * The table is guessed for joins
     *
     * @test
     */
    public function the_table_is_guessed_for_joins(): void
    {
        $this->setSchema(/* @lang GraphQL */ <<<'SDL'
type Query {
    items(
        orderBy: [OrderByClause!]
            @safeOrderBy
            @builder(method: "Tests\\LighthouseHelpers\\OrderDirectivesTestBuilder@joinChildren")
    ): [TestItem!]
        @all(model: "TestItem")
}

type TestItem {
    name: String!
}
SDL
        );

        TestItem::query()->forceCreate([
            'name' => 'aaa',
        ]);
        TestItem::query()->forceCreate([
            'name' => 'zzz',
        ]);

        $this->graphQL('
            {
                items(orderBy: [{field: "name", direction: DESC}]) {
                    name
                }
            }
        ')->assertJson(['data' => ['items' => [
            ['name' => 'zzz'],
            ['name' => 'aaa'],
        ]]]);
    }

    /**
     * The table can be specified
     *
     * @test
     */
    public function the_table_can_be_specified(): void
    {
        $this->setSchema(/* @lang GraphQL */ <<<'SDL'
type Query {
    items(
        orderBy: [OrderByClause!]
            @safeOrderBy(table: "test_children")
            @builder(method: "Tests\\LighthouseHelpers\\OrderDirectivesTestBuilder@joinChildren")
    ): [TestItem!]
        @all(model: "TestItem")
}

type TestItem {
    name: String!
}
SDL
        );

        TestItem::query()->forceCreate([
            'name' => 'zzz',
        ])->children()->create([
            'name' => 'aaa',
        ]);
        TestItem::query()->forceCreate([
            'name' => 'aaa',
        ])->children()->create([
            'name' => 'zzz',
        ]);

        $this->graphQL('
            {
                items(orderBy: [{field: "name", direction: DESC}]) {
                    name
                }
            }
        ')->assertJson(['data' => ['items' => [
            ['name' => 'aaa'],
            ['name' => 'zzz'],
        ]]]);
    }
}

class OrderDirectivesTestBuilder
{
    public function joinChildren(Builder $builder): Builder
    {
        return $builder->select('test_items.*')
            ->leftJoin('test_children', 'test_children.item_id', '=', 'test_items.id');
    }
}
