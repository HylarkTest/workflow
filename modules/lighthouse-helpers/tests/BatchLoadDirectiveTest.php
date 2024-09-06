<?php

declare(strict_types=1);

namespace Tests\LighthouseHelpers;

use App\Models\TestItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Nuwave\Lighthouse\GlobalId\GlobalId;
use Illuminate\Database\Schema\Blueprint;

class BatchLoadDirectiveTest extends TestCase
{
    /**
     * Models can be batch loaded from global ids
     *
     * @test
     */
    public function models_can_be_batch_loaded_from_global_ids(): void
    {
        $this->setSchema(/* @lang GraphQL */ <<<'SDL'
type Query {
    items: [TestItem!]  @all
}

type TestItem {
    id: ID! @globalId(type: "TestItem")
    name: String!
    child: TestChild @batchLoad(attribute: "child_global_id")
}

type TestChild {
    id: ID! @globalId(type: "TestChild")
    name: String
}
SDL
        );
        $firstItem = TestItem::query()->forceCreate([
            'name' => 'Larry',
        ]);
        $secondItem = TestItem::query()->forceCreate([
            'name' => 'Toby',
        ]);
        $firstChild = $firstItem->children()->create(['name' => 'Larry\'s child']);
        $secondChild = $secondItem->children()->create(['name' => 'Toby\'s child']);

        $firstItem->child_global_id = $firstChild->global_id;
        $secondItem->child_global_id = $secondChild->global_id;

        $firstItem->save();
        $secondItem->save();

        DB::enableQueryLog();

        $this->graphQL('
            {
                items {
                    id
                    name
                    child {
                        id
                        name
                    }
                }
            }
        ')->assertJson(['data' => [
            'items' => [
                [
                    'id' => $firstItem->global_id,
                    'name' => 'Larry',
                    'child' => ['id' => $firstChild->global_id, 'name' => 'Larry\'s child'],
                ],
                [
                    'id' => $secondItem->global_id,
                    'name' => 'Toby',
                    'child' => ['id' => $secondChild->global_id, 'name' => 'Toby\'s child'],
                ],
            ],
        ]]);

        // Expecting 2 queries, one for the items and one for all the children
        static::assertCount(2, DB::getQueryLog());
    }

    /**
     * The batch load directive can handle different classes
     *
     * @test
     */
    public function the_batch_load_directive_can_handle_different_classes(): void
    {
        $this->setSchema(/* @lang GraphQL */ <<<'SDL'
type Query {
    items: [TestItem!]  @all
}

type TestItem {
    id: ID! @globalId(type: "TestItem")
    name: String!
    child: Child @batchLoad(attribute: "child_global_id")
}

interface Child {
    name: String
}

type TestChild implements Child {
    id: ID! @globalId(type: "TestChild")
    name: String
}

type OtherTestChild implements Child {
    id: ID! @globalId(type: "OtherTestChild")
    name: String
}
SDL
        );
        $firstItem = TestItem::query()->forceCreate([
            'name' => 'Larry',
        ]);
        $secondItem = TestItem::query()->forceCreate([
            'name' => 'Toby',
        ]);
        $firstChild = $firstItem->children()->create(['name' => 'Larry\'s child']);
        $secondChild = $secondItem->children()->create(['name' => 'Toby\'s child']);

        $firstItem->child_global_id = $firstChild->global_id;
        $secondItem->child_global_id = resolve(GlobalId::class)->encode('OtherTestChild', $secondChild->id);

        $firstItem->save();
        $secondItem->save();

        DB::enableQueryLog();

        $this->graphQL('
            {
                items {
                    id
                    name
                    child {
                        name
                    }
                }
            }
        ')->assertJson(['data' => [
            'items' => [
                [
                    'id' => $firstItem->global_id,
                    'name' => 'Larry',
                    'child' => ['name' => 'Larry\'s child'],
                ],
                [
                    'id' => $secondItem->global_id,
                    'name' => 'Toby',
                    'child' => ['name' => 'Toby\'s child'],
                ],
            ],
        ]]);

        // Expecting 3 queries, as the batches are separated by class name
        static::assertCount(3, DB::getQueryLog());
    }

    protected function setUp(): void
    {
        parent::setUp();
        Schema::table('test_items', static function (Blueprint $table) {
            $table->string('child_global_id')->nullable();
        });
    }
}

namespace App\Models;

class OtherTestChild extends TestChild {}
