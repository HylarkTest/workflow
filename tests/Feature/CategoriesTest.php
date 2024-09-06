<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use Nuwave\Lighthouse\GlobalId\GlobalId;
use Tests\Mappings\Feature\Categories\User;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);
uses(Tests\Concerns\TestsFields::class);

test('categories can be created', function () {
    $globalId = resolve(GlobalId::class);
    /** @var User $user */
    $user = createUser();

    $this->be($user)->assertGraphQLMutation([
        'createCategory(input: { name: "Careers", items: [{ name: "Chef" }, { name: "Teacher" }] })' => [
            'code' => '200',
            'category' => [
                'id' => $globalId->encode('Category', 1),
                'name' => 'Careers',
                'items' => [
                    ['id' => $globalId->encode('CategoryItem', 1), 'name' => 'Chef'],
                    ['id' => $globalId->encode('CategoryItem', 2), 'name' => 'Teacher'],
                ],
            ],
        ],
    ]);
});

test('categories use efficient queries', function () {
    $globalId = resolve(GlobalId::class);
    /** @var User $user */
    $user = createUser();

    $category = createCategory();
    $id = $category->global_id;

    $queryCount = 0;
    DB::listen(function (QueryExecuted $e) use (&$queryCount) {
        $queryCount++;
    });
    $this->be($user)->assertGraphQL([
        'categories' => [
            'edges' => [
                ['node' => [
                    'id' => $id,
                    'name' => 'Careers',
                    'itemCount' => 3,
                    'items' => [
                        ['name' => 'Chef', 'category' => ['id' => $id]],
                        ['name' => 'Teacher', 'category' => ['id' => $id]],
                        ['name' => 'Developer', 'category' => ['id' => $id]],
                    ],
                ]],
            ],
        ],
    ]);

    /*
     * Expected queries
     * 1. Fetch base users
     * 2. Fetch mapping
     * 3. Count categories for pagination
     * 4. Fetch categories
     * 5. Fetch item counts
     * 6. Fetch items
     * 7. Fetch nested category for item
     */
    expect($queryCount)->toBe(6);
});

test('a user can delete a category', function () {
    $user = createUser();
    $category = createCategory();

    $this->be($user)->assertGraphQLMutation(
        'deleteCategory(input: $input).code',
        ['input: DeleteCategoryInput!' => [
            'id' => $category->global_id,
        ],
        ]);

    expect($category->items)->toBeEmpty();
});

test('a user can delete a category along with mapping', function () {
    $user = createUser();
    $category = createCategory();
    $mapping = $this->createMappingWithField($user, Mappings\Core\Mappings\Fields\FieldType::CATEGORY(), ['category' => $category->getKey()]);

    $this->be($user)->assertGraphQLMutation(
        'deleteCategory(input: $input).code',
        ['input: DeleteCategoryInput!' => [
            'id' => $category->global_id,
        ],
        ]);
    $field = $mapping->fresh()->fields->last();
    expect($field->type()->is(Mappings\Core\Mappings\Fields\FieldType::CATEGORY()))->toBeFalse();

});
