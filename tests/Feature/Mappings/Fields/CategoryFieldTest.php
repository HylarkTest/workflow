<?php

declare(strict_types=1);

use Tests\Concerns\TestsFields;
use Mappings\Core\Mappings\Fields\FieldType;

uses(TestsFields::class);

test('a mapping can have a category type field', function () {
    $user = createUser();
    $category = createCategory();
    $mapping = createMapping($user);

    $this->be($user)->sendAddFieldRequest($mapping, FieldType::CATEGORY(), ['category' => $category->globalId()]);

    /** @var \Mappings\Core\Mappings\Fields\Field $field */
    $field = $mapping->fresh()->fields->last();
    expect($field->name)->toBe('field');
    expect($field->type()->is(FieldType::CATEGORY()))->toBeTrue();
    expect($field->options())->toBe(['category' => $category->getKey()]);
});

test('a category field can be multi select', function () {
    $user = createUser();
    $category = createCategory();
    $mapping = createMapping($user);

    $this->be($user)->sendAddFieldRequest($mapping, FieldType::CATEGORY(), ['multiSelect' => true, 'category' => $category->globalId()]);

    /** @var \Mappings\Core\Mappings\Fields\Field $field */
    $field = $mapping->fresh()->fields->last();
    expect($field->name)->toBe('field');
    expect($field->type()->is(FieldType::CATEGORY()))->toBeTrue();
    static::assertSame([
        'category' => $category->getKey(),
        'multiSelect' => true,
    ], $field->options());
});

test('a category field can be saved on an item', function () {
    $user = createUser();
    $category = createCategory();
    /** @var \Mappings\Models\CategoryItem $categoryItem */
    $categoryItem = $category->items->first();
    $this->be($user)->assertItemCreatedWithField(
        FieldType::CATEGORY(),
        ['category' => $category->getKey()],
        ['fieldValue' => $categoryItem->globalId()],
        ['fieldValue' => ['id' => $categoryItem->globalId(), 'name' => 'Chef']],
        ['_v' => $categoryItem->id],
        'field { fieldValue { id name} }'
    );
});

test('a category field multi select can be saved on an item', function () {
    $user = createUser();
    $category = createCategory();
    /** @var \Mappings\Models\CategoryItem $categoryItem */
    $categoryItem = $category->items->first();
    /** @var \Mappings\Models\CategoryItem $categoryItem2 */
    $categoryItem2 = $category->items->get(1);
    $this->be($user)->assertItemCreatedWithField(
        FieldType::CATEGORY(),
        ['multiSelect' => true, 'category' => $category->getKey()],
        ['fieldValue' => [$categoryItem->globalId(), $categoryItem2->globalId()]],
        ['fieldValue' => [
            ['id' => $categoryItem->globalId(), 'name' => 'Chef'],
            ['id' => $categoryItem2->globalId(), 'name' => 'Teacher'],
        ]],
        ['_v' => [$categoryItem->id, $categoryItem2->id]],
        'field { fieldValue { id name} }'
    );
});

test('a category field can be updated on an item', function () {
    $user = createUser();
    $category = createCategory();
    /** @var \Mappings\Models\CategoryItem $categoryItem */
    $firstCategoryItem = $category->items->first();
    $secondCategoryItem = $category->items->last();

    $this->be($user)->assertItemUpdatedWithField(
        FieldType::CATEGORY(),
        ['category' => $category->getKey()],
        ['fieldValue' => $firstCategoryItem->globalId()],
        ['fieldValue' => $secondCategoryItem->globalId()],
        ['fieldValue' => ['id' => $secondCategoryItem->globalId(), 'name' => 'Developer']],
        ['_v' => $secondCategoryItem->id],
        'field { fieldValue { id name} }'
    );
});

test('an item from a different category cannot be saved to a category field', function () {
    $user = createUser();
    $category = createCategory();
    $otherCategory = createCategory();
    $item = $otherCategory->items->first();
    $this->be($user)->assertInvalidFieldRequest(
        FieldType::CATEGORY(),
        ['category' => $category->getKey()],
        ['field' => ['fieldValue' => $item->globalId()]],
        ['input.data.field.fieldValue' => ['The selected "field" is invalid.']],
        'field { fieldValue { id name} }'
    );
});

test('if the category changes previous values are not returned', function () {
    $user = createUser();
    $category = createCategory();
    $otherCategory = createCategory();
    $categoryItem = $category->items->first();
    $mapping = $this->createMappingWithField($user, FieldType::CATEGORY(), ['category' => $category->getKey()]);
    $item = createItem($mapping, ['fieldId' => ['_v' => $categoryItem->id]]);

    $this->be($user)->postJson(route('graphql', $mapping->globalId()), [
        'query' => "
        {
            items {
                item(id: \"$item->global_id\") {
                    data {
                        field { fieldValue { id name} }
                    }
                }
            }
        }
        ",
    ])->assertJson(['data' => ['items' => [
        'item' => ['data' => ['field' => ['fieldValue' => ['id' => $categoryItem->globalId(), 'name' => 'Chef']]]],
    ]]], true);

    tenancy()->initialize($user->firstPersonalBase());
    $fields = $mapping->fields;
    $field = $fields->last();
    $field->options = ['category' => $otherCategory->globalId()];
    $fields->splice(-1, 1, [$field]);
    $mapping->fields = $fields;
    $mapping->save();

    $this->postJson(route('graphql', $mapping->globalId()), [
        'query' => "
        {
            items {
                item(id: \"$item->global_id\") {
                    data {
                        field { fieldValue { id name} }
                    }
                }
            }
        }
        ",
    ])->assertJson(['data' => ['items' => [
        'item' => ['data' => ['field' => ['fieldValue' => null]]],
    ]]], true);
});

test('a category field is not required by default', function () {
    $user = createUser();
    $category = createCategory();
    $this->be($user)->assertValidFieldRequest(
        FieldType::CATEGORY(),
        ['category' => $category->globalId()],
        ['name' => ['fieldValue' => 'Larry']],
        ['field' => null],
        'field { fieldValue { id name} }'
    );
});

test('a category field can be made required', function () {
    $user = createUser();
    $category = createCategory();
    $this->be($user)->assertInvalidFieldRequest(
        FieldType::CATEGORY(),
        ['category' => $category->globalId(), 'rules' => ['required' => true]],
        ['name' => ['fieldValue' => 'Larry']],
        ['input.data.field.fieldValue' => ['The "field" field is required.']],
        'field { fieldValue { id name} }'
    );
});

test('the category field must include a category id', function () {
    $this->assertInvalidAddFieldRequest(
        FieldType::CATEGORY(),
        [],
        ['input.options.category' => ['The category field is required.']],
    );
});

test('the category id must be a valid category', function () {
    $this->assertInvalidAddFieldRequest(
        FieldType::CATEGORY(),
        ['category' => 'invalid_id'],
        ['input.options.category' => ['The selected category is invalid.']],
    );
});

test('category fields can have labels', function () {
    $user = createUser();
    $category = createCategory();

    /** @var \Mappings\Models\CategoryItem $categoryItem */
    $categoryItem = $category->items->first();
    $this->be($user)->assertItemCreatedWithField(
        FieldType::CATEGORY(),
        ['category' => $category->getKey(), 'labeled' => ['freeText' => true]],
        ['label' => 'Label', 'fieldValue' => $categoryItem->globalId()],
        ['label' => 'Label', 'fieldValue' => ['id' => $categoryItem->globalId(), 'name' => 'Chef']],
        ['label' => 'Label', 'fieldValue' => $categoryItem->id],
        'field { label fieldValue { id name } }'
    );
});

test('a category field can have labels with multi', function () {
    $user = createUser();
    $category = createCategory();

    /** @var \Mappings\Models\CategoryItem $categoryItem */
    $categoryItem = $category->items->first();
    $this->be($user)->assertItemCreatedWithField(
        FieldType::CATEGORY(),
        [
            'labeled' => ['freeText' => true],
            'category' => $category->getKey(),
            'multiSelect' => true,
        ],
        ['label' => 'Label', 'fieldValue' => [$categoryItem->globalId()]],
        ['label' => 'Label', 'fieldValue' => [['id' => $categoryItem->globalId(), 'name' => 'Chef']]],
        ['label' => 'Label', 'fieldValue' => [$categoryItem->id]],
        'field { label fieldValue { id name } }'
    );
});

test('category field actions are formatted correctly', function () {
    $user = createUser();
    $category = createCategory();

    /** @var \Mappings\Models\CategoryItem $categoryItem */
    $categoryItem = $category->items->first();
    $otherCategoryItem = $category->items->last();

    $this->be($user)->assertItemUpdateCreatedActions(
        FieldType::CATEGORY(),
        ['category' => $category->getKey()],
        ['fieldValue' => $categoryItem->id],
        ['fieldValue' => $otherCategoryItem->id],
        ['after' => 'Chef'],
        ['before' => 'Chef', 'after' => 'Developer'],
    );
});

test('multi select category field actions are formatted correctly', function () {
    $user = createUser();
    $category = createCategory();

    /** @var \Mappings\Models\CategoryItem $categoryItem */
    $categoryItem = $category->items->first();
    $otherCategoryItem = $category->items->last();

    $this->be($user)->assertItemUpdateCreatedActions(
        FieldType::CATEGORY(),
        ['multiSelect' => true, 'category' => $category->getKey()],
        ['fieldValue' => [$categoryItem->id]],
        ['fieldValue' => [$categoryItem->id, $otherCategoryItem->id]],
        ['after' => 'Chef'],
        ['before' => 'Chef', 'after' => 'Chef, Developer'],
    );
});

test('labeled multi select category field actions are formatted correctly', function () {
    $user = createUser();
    $category = createCategory();

    /** @var \Mappings\Models\CategoryItem $categoryItem */
    $categoryItem = $category->items->first();
    $otherCategoryItem = $category->items->last();

    $this->be($user)->assertItemUpdateCreatedActions(
        FieldType::CATEGORY(),
        ['multiSelect' => true, 'labeled' => ['freeText' => true], 'category' => $category->getKey()],
        ['label' => 'Test', 'fieldValue' => [$categoryItem->id]],
        ['label' => 'Test2', 'fieldValue' => [$categoryItem->id, $otherCategoryItem->id]],
        ['after' => '[Test]: Chef'],
        ['before' => '[Test]: Chef', 'after' => '[Test2]: Chef, Developer'],
    );
});

test('list multi select category field actions are formatted correctly', function () {
    $user = createUser();
    $category = createCategory();

    /** @var \Mappings\Models\CategoryItem $categoryItem */
    $categoryItem = $category->items->first();
    $otherCategoryItem = $category->items->last();

    $this->be($user)->assertItemUpdateCreatedActions(
        FieldType::CATEGORY(),
        ['multiSelect' => true, 'list' => true, 'category' => $category->getKey()],
        ['listValue' => [['fieldValue' => [$categoryItem->id]]]],
        ['listValue' => [['fieldValue' => [$otherCategoryItem->id]], ['fieldValue' => [$categoryItem->id, $otherCategoryItem->id]]]],
        ['after' => 'Chef', 'type' => 'paragraph'],
        ['before' => 'Chef', 'after' => "Developer\nChef, Developer", 'type' => 'paragraph'],
    );
});

test('list labeled multi select category field actions are formatted correctly', function () {
    $user = createUser();
    $category = createCategory();

    /** @var \Mappings\Models\CategoryItem $categoryItem */
    $categoryItem = $category->items->first();
    $otherCategoryItem = $category->items->last();

    $this->be($user)->assertItemUpdateCreatedActions(
        FieldType::CATEGORY(),
        ['multiSelect' => true, 'labeled' => ['freeText' => true], 'list' => true, 'category' => $category->getKey()],
        ['listValue' => [['label' => 'Test', 'fieldValue' => [$categoryItem->id]]]],
        ['listValue' => [
            ['label' => 'TestA', 'fieldValue' => [$otherCategoryItem->id]],
            ['label' => 'Test2', 'fieldValue' => [$categoryItem->id, $otherCategoryItem->id]],
        ]],
        ['after' => '[Test]: Chef', 'type' => 'paragraph'],
        ['before' => '[Test]: Chef', 'after' => "[TestA]: Developer\n[Test2]: Chef, Developer", 'type' => 'paragraph'],
    );
});

test('items can be sorted by category', function () {
    $user = createUser();
    $category = createCategory();
    [$chefItem, $teacherItem, $developerItem] = $category->items;
    $mapping = $this->createMappingWithField($user, FieldType::CATEGORY(), ['category' => $category->getKey()]);

    $items = $this->createSortableItems($mapping, [
        $chefItem->getKey(),
        $teacherItem->getKey(),
        $developerItem->getKey(),
    ]);

    $this->be($user)->assertGraphQL(
        ['items' => [
            'items(orderBy: [{ field: "field:fieldId", direction: DESC }])' => [
                'edges' => array_map(
                    fn ($index) => ['node' => ['id' => $items[$index]->globalId()]],
                    [1, 2, 0]
                ),
            ],
        ]],
    );
    $this->be($user)->assertGraphQL(
        ['items' => [
            'items(orderBy: [{ field: "field:fieldId", direction: ASC }])' => [
                'edges' => array_map(
                    fn ($index) => ['node' => ['id' => $items[$index]->globalId()]],
                    [0, 2, 1]
                ),
            ],
        ]],
    );
})->group('es');

test('items cannot be sorted by multiselect categories', function () {
    $this->assertFieldIsNotSortable(
        FieldType::CATEGORY(),
        ['multiSelect' => true],
    );
});
