<?php

declare(strict_types=1);

namespace Tests\LighthouseHelpers;

use App\Models\TestItem;

class ConditionalRelationDirectiveTest extends TestCase
{
    /**
     * The relationship can be specified by an argument
     *
     * @test
     */
    public function the_relationship_can_be_specified_by_an_argument(): void
    {
        $this->setSchema(/* @lang GraphQL */ <<<'SDL'
type Query {
    item(id: ID! @eq): TestItem @find(model: "SuperTestItem")
}

type TestItem {
    name: String!
    children(reverse: Boolean): [TestChildren]! @conditionalRelation(arg: "reverse", trueRelation: "reversedChildren")
}

type TestChildren {
    name: String!
}
SDL
        );

        TestItem::query()->forceCreate([
            'name' => 'Larry',
        ])->children()->createMany([
            ['name' => 'aaa'],
            ['name' => 'zzz'],
        ]);

        $this->graphQL('
            {
                item(id: 1) {
                    children { name }
                    reversedChildren: children(reverse: true) { name }
                }
            }
        ')->assertJson(['data' => ['item' => [
            'children' => [['name' => 'aaa'], ['name' => 'zzz']],
            'reversedChildren' => [['name' => 'zzz'], ['name' => 'aaa']],
        ]]]);
    }
}

namespace App\Models;

class SuperTestItem extends TestItem
{
    public function reversedChildren(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->children()->orderByDesc('id');
    }
}
