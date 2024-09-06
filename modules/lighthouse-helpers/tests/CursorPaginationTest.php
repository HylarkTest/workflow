<?php

declare(strict_types=1);

namespace Tests\LighthouseHelpers;

use App\Models\TestItem;
use App\Models\TestChild;
use LighthouseHelpers\Pagination\Cursor;
use Nuwave\Lighthouse\Execution\BatchLoader\BatchLoaderRegistry;

class CursorPaginationTest extends TestCase
{
    /**
     * Results can be queried by paginated with cursor
     *
     * @test
     */
    public function results_can_be_queried_by_paginated_with_cursor(): void
    {
        TestItem::query()->insert(collect(range(1, 10))->map(fn () => ['name' => $this->faker->word])->all());

        $json = $this->graphQL('
            {
                items(first: 8) {
                    edges {
                        cursor
                    }
                    pageInfo {
                        total
                        count
                    }
                }
            }
        ')->json();

        $cursor = data_get($json, 'data.items.edges.5.cursor');
        $pageInfo = data_get($json, 'data.items.pageInfo');

        static::assertSame(10, $pageInfo['total']);
        static::assertSame(8, $pageInfo['count']);

        /*
         * This will give cursors for items 1 through to 10
         * $cursor will be the cursor for the 6th item in the list.
         * As they are ordered by id this should be the item with id = 6.
         */
        $id = $this->graphQL("
            {
                items(first: 5, after: \"$cursor\") {
                    edges {
                        node {
                            id
                        }
                    }
                }
            }
        ")->json('data.items.edges.0.node.id');

        /*
         * Now we are getting all the items after the 6th.
         * The first of which should have an id of 7.
         */
        static::assertSame('TestItem:7', base64_decode($id, true));
    }

    /**
     * Ordered results can be paginated with a cursor
     *
     * @test
     */
    public function ordered_results_can_be_paginated_with_a_cursor(): void
    {
        TestItem::query()->insert(collect(range(1, 10))->map(fn ($n) => ['name' => \chr(96 + $n)])->all());

        $cursor = $this->graphQL('
            {
                items(first: 10, orderBy: [{field: "name", direction: DESC}]) {
                    edges {
                        cursor
                    }
                }
            }
        ')->json('data.items.edges.5.cursor');

        /*
         * Now we are ordering the items by name in descending order.
         * This should give the items in reverse order so $cursor will be the
         * 6th item from 10 which is the item with id = 5.
         */
        $id = $this->graphQL("
            {
                items(first: 5, after: \"$cursor\", orderBy: [{field: \"name\", direction: DESC}]) {
                    edges {
                        node {
                            id
                        }
                    }
                }
            }
        ")->json('data.items.edges.0.node.id');

        /*
         * Now we want to get the item after the last cursor with id = 5.
         * As we are going in descending order that should be the item with
         * id = 4.
         */
        static::assertSame('TestItem:4', base64_decode($id, true));
    }

    /**
     * Relations can be paginated
     *
     * @test
     */
    public function relations_can_be_paginated(): void
    {
        $item = TestItem::query()->create(['name' => 'a']);

        TestChild::query()->insert(
            collect(range(1, 10))->map(
                fn () => ['name' => $this->faker->word, 'item_id' => $item->id]
            )->all()
        );

        $gId = $item->globalId();
        $cursor = $this->graphQL("
            {
                item(id: \"$gId\") {
                    children(first: 10) {
                        edges {
                            cursor
                        }
                    }
                }
            }
        ")->json('data.item.children.edges.5.cursor');

        BatchLoaderRegistry::forgetInstances();

        $id = $this->graphQL("
            {
                item(id: \"$gId\") {
                    children(first: 5, after: \"$cursor\") {
                        edges {
                            node {
                                id
                            }
                        }
                    }
                }
            }
        ")->json('data.item.children.edges.0.node.id');

        static::assertSame('TestChild:7', base64_decode($id, true));
    }

    /**
     * Relations can be ordered and paginated
     *
     * @test
     */
    public function relations_can_be_ordered_and_paginated(): void
    {
        $item = TestItem::query()->create(['name' => 'a']);

        TestChild::query()->insert(collect(range(1, 10))->map(
            fn ($n) => ['name' => \chr(96 + $n), 'item_id' => $item->id]
        )->all());

        $gId = $item->globalId();
        $cursor = $this->graphQL("
            {
                item(id: \"$gId\") {
                    children(first: 10, orderBy: [{field: \"name\", direction: DESC}]) {
                        edges {
                            cursor
                        }
                    }
                }
            }
        ")->json('data.item.children.edges.5.cursor');

        app()->forgetInstance('item_children');

        $id = $this->graphQL("
            {
                item(id: \"$gId\") {
                    children(first: 5, after: \"$cursor\", orderBy: [{field: \"name\", direction: DESC}]) {
                        edges {
                            node {
                                id
                            }
                        }
                    }
                }
            }
        ")->json('data.item.children.edges.0.node.id');

        static::assertSame('TestChild:4', base64_decode($id, true));
    }

    /**
     * Relations can be grouped and paginated
     *
     * @test
     */
    public function relations_can_be_grouped_and_paginated(): void
    {
        $firstItem = TestItem::query()->create(['name' => 'a']);
        $secondItem = TestItem::query()->create(['name' => 'b']);

        TestChild::query()->insert(collect(range(1, 10))->map(
            fn ($n) => ['name' => $this->faker->word, 'item_id' => ($n % 2 ? $firstItem : $secondItem)->id]
        )->all());

        $cursor = Cursor::encode(['id' => 3]);
        $ids = $this->graphQL("
            {
                items(first: 2) {
                    edges {
                        node {
                            children(first: 3, after: \"$cursor\") {
                                edges {
                                    node {
                                        id
                                    }
                                }
                            }
                        }
                    }
                }
            }
        ")->json('data.items.edges.*.node.children.edges.*.node.id');

        static::assertSame([
            base64_encode('TestChild:5'),
            base64_encode('TestChild:7'),
            base64_encode('TestChild:9'),
            base64_encode('TestChild:4'),
            base64_encode('TestChild:6'),
            base64_encode('TestChild:8'),
        ], $ids);
    }

    protected function setUp(): void
    {
        $this->setSchema(/* @lang GraphQL */ <<<'SDL'
type Query {
    items(orderBy: [OrderByClause!] @safeOrderBy): [TestItem!] @paginate
    item(id: ID! @globalId(decode: "ID", type: "TestItem") @eq): TestItem @find
}

type TestItem @node {
    name: String!
    children(orderBy: [OrderByClause!] @safeOrderBy): [TestChild!] @hasMany(type: "CONNECTION")
}

type TestChild @node {
    name: String!
}
SDL
        );

        parent::setUp();
    }
}
