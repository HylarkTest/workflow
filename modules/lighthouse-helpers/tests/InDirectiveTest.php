<?php

declare(strict_types=1);

namespace Tests\LighthouseHelpers;

use App\Models\TestItem;

class InDirectiveTest extends TestCase
{
    /**
     * The in directive is ignored with a null value
     *
     * @test
     */
    public function the_in_directive_is_ignored_with_a_null_value(): void
    {
        $this->setSchema(/* @lang GraphQL */ <<<'SDL'
type Query {
    items(name: [String!] @in): [TestItem!] @all(model: "TestItem") @orderable
}

type TestItem {
    name: String!
}
SDL
        );

        TestItem::query()->forceCreate([
            'name' => 'Larry',
        ]);

        $this->graphQL('
            {
                itemsWithFilter: items(name: ["Larry"]) {
                    name
                }
                itemsWithEmptyFilter: items(name: []) {
                    name
                }
                itemsWithNull: items(name: null) {
                    name
                }
            }
        ')
            ->assertJsonCount(1, 'data.itemsWithFilter')
            ->assertJsonCount(0, 'data.itemsWithEmptyFilter')
            ->assertJsonCount(1, 'data.itemsWithNull');
    }
}
