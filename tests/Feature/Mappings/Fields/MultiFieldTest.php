<?php

declare(strict_types=1);

use Illuminate\Support\Arr;
use Mappings\Models\Category;
use Tests\Concerns\TestsFields;
use Mappings\Core\Mappings\Fields\FieldType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mappings\Core\Mappings\Fields\Types\LineField;

uses(RefreshDatabase::class);
uses(TestsFields::class);

test('a mapping can have a multi type field', function () {
    $this->assertFieldCreated(FieldType::MULTI(), ['fields' => [[
        'name' => 'nested field',
        'type' => FieldType::SELECT()->key,
        'id' => 'field',
        'apiName' => 'nestedField',
        'options' => ['valueOptions' => ['a' => 'b']],
        'meta' => null,
        'section' => null,
        'createdAt' => (string) now(),
        'updatedAt' => (string) now(),
    ]]]);
});

test('the field order is the same as when it is saved', function () {
    $user = createUser();
    $mapping = createMapping($user);

    $this->be($user)->sendAddFieldRequest($mapping, FieldType::MULTI(), ['fields' => [
        [
            'meta' => [],
            'name' => 'First',
            'options' => [],
            'type' => FieldType::LINE()->key,
            'val' => 'LINE',
        ],
        [
            'meta' => ['display' => 'CHECKBOX'],
            'name' => 'Second',
            'options' => ['list' => false, 'labeled' => false],
            'type' => FieldType::BOOLEAN()->key,
            'val' => 'CHECKBOX',
        ],
    ]])->assertSuccessfulGraphQL();

    $fields = $mapping->fresh()->fields->last()->fields();
    expect($fields->get(0)->name)->toBe('First');
    expect($fields->get(1)->name)->toBe('Second');
});

test('a multi field can be updated', function () {
    $user = createUser();
    $mapping = $this->createMappingWithField($user, FieldType::MULTI(), [
        'fields' => [[
            'name' => 'nestedField',
            'type' => FieldType::LINE()->key,
        ]],
    ]);

    $this->be($user)->postGraphQL([
        'query' => '
        mutation($input: UpdateMappingFieldInput!) {
            updateMappingField(input: $input) { code mapping { id } }
        }
        ',
        'variables' => [
            'input' => [
                'mappingId' => $mapping->globalId(),
                'id' => $mapping->fields->last()->id,
                'name' => 'field',
                'options' => ['fields' => [
                    [
                        'name' => 'nestedParagraph',
                        'type' => FieldType::PARAGRAPH()->key,
                    ],
                    [
                        'name' => 'newNestedParagraph',
                        'type' => FieldType::LINE()->key,
                    ],
                ]],
            ],
        ],
    ])->assertJson(['data' => ['updateMappingField' => ['mapping' => ['id' => $mapping->globalId()]]]]);

    $fieldsArray = json_decode($mapping->fresh()->getRawOriginal('fields'), true);
    $multiField = Arr::last($fieldsArray);
    expect($multiField['options']['fields'])
        ->toHaveCount(2)
        ->each->toHaveKey('id');
});

test('a multi field can be saved on an item', function () {
    $this->assertItemCreatedWithField(
        FieldType::MULTI(),
        ['fields' => [[
            'id' => 'fieldId',
            'name' => 'Nested field',
            'type' => FieldType::LINE()->key,
        ]]],
        ['fieldValue' => ['nestedField' => ['fieldValue' => 'The field value.']]],
        null,
        ['_v' => ['fieldId' => ['_v' => 'The field value.']]],
        'field { fieldValue { nestedField { fieldValue } } }'
    );
});

test('multi fields and nested fields can be labeled', function () {
    $this->assertItemCreatedWithField(
        FieldType::MULTI(),
        [
            'fields' => [[
                'id' => 'fieldId',
                'name' => 'Nested field',
                'type' => FieldType::LINE()->key,
                'options' => ['labeled' => ['freeText' => true]],
            ]],
            'labeled' => ['freeText' => true],
        ],
        [
            'fieldValue' => [
                'nestedField' => ['fieldValue' => 'The field value.', 'label' => 'Nested label'],
            ],
            'label' => 'Field label',
        ],
        null,
        ['_v' => ['fieldId' => ['_v' => 'The field value.', '_l' => 'Nested label']], '_l' => 'Field label'],
        'field { fieldValue { nestedField { fieldValue label } } label }'
    );
});

test('labeled nested fields must have label options', function () {
    $this->assertInvalidAddFieldRequest(
        FieldType::MULTI(),
        [
            'fields' => [[
                'id' => 'fieldId',
                'name' => 'Nested field',
                'type' => FieldType::LINE()->key,
                'options' => ['labeled' => ['freeText' => false, 'labels' => ['1' => '']]],
            ]],
        ],
        ['input.options.fields.0.options.labeled.labels.1' => ['Please provide label options.']],
    );
});

test('a multi field can be updated on an item', function () {
    $this->assertItemUpdatedWithField(
        FieldType::MULTI(),
        ['fields' => [[
            'id' => 'fieldId',
            'name' => 'Nested field',
            'type' => FieldType::LINE()->key,
        ]]],
        ['_v' => ['fieldId' => ['_v' => 'The field value.']]],
        ['fieldValue' => ['nestedField' => ['fieldValue' => 'The updated field value.']]],
        null,
        ['_v' => ['fieldId' => ['_v' => 'The updated field value.']]],
        'field { fieldValue { nestedField { fieldValue } } }'
    );
});

test('updating a field does not clear other fields', function () {
    $this->assertItemUpdatedWithField(
        FieldType::MULTI(),
        ['fields' => [
            [
                'id' => 'fieldId',
                'name' => 'Nested field',
                'type' => FieldType::LINE()->key,
            ],
            [
                'id' => 'otherId',
                'name' => 'Other field',
                'type' => FieldType::LINE()->key,
            ],
        ]],
        ['_v' => [
            'otherId' => ['_v' => 'The other value'],
            'fieldId' => ['_v' => 'The field value.'],
        ]],
        ['fieldValue' => ['nestedField' => ['fieldValue' => 'The updated field value.']]],
        null,
        ['_v' => [
            'otherId' => ['_v' => 'The other value'],
            'fieldId' => ['_v' => 'The updated field value.'],
        ]],
        'field { fieldValue { otherField { fieldValue } nestedField { fieldValue } } }'
    );
});

test('a multi field is not required by default', function () {
    $this->assertValidFieldRequest(
        FieldType::MULTI(),
        ['fields' => [[
            'name' => 'Nested field',
            'type' => FieldType::LINE()->key,
        ]]],
        ['name' => ['fieldValue' => 'Larry']],
        ['field' => null],
        'field { fieldValue { nestedField { fieldValue } } }'
    );
});

test('a multi field can be made required', function () {
    $this->assertInvalidFieldRequest(
        FieldType::MULTI(),
        [
            'fields' => [[
                'name' => 'Nested field',
                'type' => FieldType::LINE()->key,
            ]],
            'rules' => ['required' => true],
        ],
        ['name' => ['fieldValue' => 'Larry']],
        ['input.data.field.fieldValue' => ['The "field" field is required.']],
        'field { fieldValue { nestedField { fieldValue } } }'
    );
});

test('a nested field has the default restrictions', function () {
    $longValue = str_repeat('a', LineField::MAX_LENGTH + 1);
    $this->assertInvalidFieldRequest(
        FieldType::MULTI(),
        ['fields' => [[
            'name' => 'Nested field',
            'type' => FieldType::LINE()->key,
        ]]],
        ['field' => ['fieldValue' => [
            'nestedField' => ['fieldValue' => $longValue],
        ]]],
        [
            'input.data.field.fieldValue.nestedField.fieldValue' => ['The "Nested field" must not be greater than 500 characters.'],
        ],
        'field { fieldValue { nestedField { fieldValue } } }'
    );
});

test('the multi field nested fields can be customized', function () {
    $longValue = str_repeat('a', 100 + 1);
    $this->assertInvalidFieldRequest(
        FieldType::MULTI(),
        ['fields' => [[
            'name' => 'Nested field',
            'type' => FieldType::LINE()->key,
            'options' => ['rules' => ['max' => 100]],
        ]]],
        ['field' => ['fieldValue' => [
            'nestedField' => ['fieldValue' => $longValue],
        ]]],
        [
            'input.data.field.fieldValue.nestedField.fieldValue' => ['The "Nested field" must not be greater than 100 characters.'],
        ],
        'field { fieldValue { nestedField { fieldValue } } }'
    );
});

test('the multi field nested fields options are validated as usual', function () {
    $this->assertInvalidAddFieldRequest(
        FieldType::MULTI(),
        ['fields' => [[
            'name' => 'Nested field',
            'type' => FieldType::LINE()->key,
            'options' => ['rules' => ['max' => LineField::MAX_LENGTH + 1]],
        ]]],
        ['input.options.fields.0.options.rules.max' => ['The max rule must not be greater than 500.']],
    );
});

test('specific multi fields can be required', function () {
    $this->assertInvalidFieldRequest(
        FieldType::MULTI(),
        ['fields' => [
            [
                'name' => 'Nested field',
                'type' => FieldType::LINE()->key,
                'options' => ['rules' => ['required' => true]],
            ],
            [
                'name' => 'Other nested field',
                'type' => FieldType::LINE()->key,
            ],
        ]],
        ['field' => ['fieldValue' => ['otherNestedField' => ['fieldValue' => 'non required value']]]],
        ['input.data.field.fieldValue.nestedField.fieldValue' => ['The "Nested field" field is required.']],
        'field { fieldValue { nestedField { fieldValue } otherNestedField { fieldValue } } }'
    );
});

test('the fields option must be valid', function () {
    $this->assertInvalidAddFieldRequest(
        FieldType::MULTI(),
        ['fields' => [[
            'name' => '',
            'apiName' => '',
            'type' => 'NOT_A_TYPE',
            'options' => 'invalid options',
            'meta' => 'invalid meta',
        ]]],
        [
            'input.options.fields.0.apiName' => [
                'The API name field must have a value.',
            ],
            'input.options.fields.0.type' => [
                'The selected type is invalid.',
            ],
            'input.options.fields.0.name' => [
                'The name field must have a value.',
            ],
            'input.options.fields.0.meta' => [
                'The meta must be an array.',
            ],
        ],
    );
});

test('the multi field has a unique type', function () {
    $user = createUser();
    $mapping = $this->createMappingWithField(
        $user,
        FieldType::MULTI(),
        ['fields' => [[
            'name' => 'Nested field',
            'type' => FieldType::LINE()->key,
        ]]],
    );

    $type = $mapping->graphql_type;
    $fieldName = 'Field';

    $json = $this->be($user)->postJson('graphql', [
        'query' => "
        {
            __type(name: \"{$type}{$fieldName}Multi\") {
                name
                fields {
                    name
                    type { name }
                }
            }
        }
        ",
    ])->json('data.__type.fields.0');

    expect($json)->toBe(['name' => 'nestedField', 'type' => ['name' => 'StringValue']]);
});

test('the multi field cannot have nested multi fields', function () {
    $this->assertInvalidAddFieldRequest(
        FieldType::MULTI(),
        ['fields' => [[
            'id' => 'fieldId',
            'name' => 'Nested field',
            'type' => FieldType::MULTI()->key,
            'options' => ['fields' => [[
                'id' => 'nestedFieldId',
                'name' => 'Double nested field',
                'type' => FieldType::LINE()->key,
            ]]],
        ]]],
        ['input.options.fields.0.type' => ['The selected type is invalid.']],
    );
});

test('the multi field must have fields', function () {
    $this->assertInvalidAddFieldRequest(
        FieldType::MULTI(),
        ['fields' => []],
        ['input.options.fields' => ['The sub fields field is required.']],
    );
});

test('nested fields are resolved correctly', function () {
    $this->fetchItemRequest(
        FieldType::MULTI(),
        ['fields' => [[
            'id' => 'fieldId',
            'name' => 'Nested field',
            'type' => FieldType::LINE()->key,
        ]]],
        ['_v' => ['fieldId' => ['_v' => 'Larry']]],
        'data {
                field { fieldValue {
                    nestedField(truncate: 3) { fieldValue }
                } }
        }',
        ['data' => [
            'field' => ['fieldValue' => ['nestedField' => ['fieldValue' => 'Lar...']]],
        ]]
    );
});

test('nested fields are serialized correctly', function () {
    $user = createUser();
    /** @var \Mappings\Models\Category $category */
    $category = Category::query()->forceCreate(['name' => 'Careers']);
    $items = $category->items()->createMany(collect(['Chef', 'Teacher', 'Developer'])->map(fn ($career) => ['name' => $career]));

    $this->be($user)->assertItemCreatedWithField(
        FieldType::MULTI(),
        ['fields' => [[
            'id' => 'fieldId',
            'name' => 'Nested field',
            'type' => FieldType::CATEGORY()->key,
            'options' => ['category' => $category->globalId()],
        ]]],
        ['fieldValue' => ['nestedField' => ['fieldValue' => $items->first()->globalId()]]],
        ['fieldValue' => ['nestedField' => ['fieldValue' => ['id' => $items->first()->globalId(), 'name' => 'Chef']]]],
        ['_v' => ['fieldId' => ['_v' => $items->first()->id]]],
        'field { fieldValue { nestedField { fieldValue { id name} } } }'
    );
});

test('list multi fields can have empty list subfields', function () {
    $user = createUser();

    $this->be($user)->assertItemCreatedWithField(
        FieldType::MULTI(),
        ['labeled' => ['freeText' => true], 'list' => true, 'fields' => [[
            'id' => 'fieldId',
            'name' => 'Nested field',
            'type' => FieldType::LINE()->key,
            'options' => ['list' => true],
        ]]],
        ['listValue' => [['label' => 'Label', 'fieldValue' => ['nestedField' => ['listValue' => null]]]]],
        ['listValue' => [['label' => 'Label', 'fieldValue' => ['nestedField' => null]]]],
        expectedValue: ['_c' => [['_l' => 'Label', '_v' => ['fieldId' => null]]]],
        request: 'field { listValue { label fieldValue { nestedField { listValue { fieldValue } } } } }',
    );
});

test('multi field actions are formatted correctly', function () {
    enableAllActions();

    $user = auth()->user() ?? createUser();
    $mapping = $this->createMappingWithField($user, FieldType::MULTI(), ['fields' => [
        [
            'id' => 'nestedFieldId',
            'name' => 'Nested field',
            'type' => FieldType::LINE()->key,
            'options' => ['labeled' => ['freeText' => true]],
        ],
        [
            'id' => 'nestedFieldId2',
            'name' => 'Nested field 2',
            'type' => FieldType::SELECT()->key,
            'options' => ['valueOptions' => ['foo', 'bar', 'baz']],
        ],
    ]]);

    $item = createItem($mapping, $this->formatExpectedValue([
        'fieldId' => ['fieldValue' => ['nestedFieldId' => ['fieldValue' => 'Larry', 'label' => 'Name']]],
    ]));
    $item->wasRecentlyCreated = false;

    $item->update($this->formatExpectedValue([
        'data' => ['fieldId' => ['fieldValue' => [
            'nestedFieldId' => ['fieldValue' => 'Toby', 'label' => 'Name'],
            'nestedFieldId2' => ['fieldValue' => 1],
        ]]],
    ]));

    $actions = $item->actions;
    expect($actions)->toHaveCount(2);

    /** @var \App\Models\Action $createAction */
    $createAction = $actions->last();
    $updateAction = $actions->first();
    expect($createAction->description(false))->toBe('Item "Item" created');
    static::assertSame([
        [
            'description' => 'Added the "field" → "Nested field"',
            'before' => null,
            'after' => '[Name]: Larry',
            'type' => 'line',
        ],
    ], $createAction->changes());

    expect($updateAction->description(false))->toBe('Item "Item" updated');
    static::assertSame([
        [
            'description' => 'Changed the "field" → "Nested field"',
            'before' => '[Name]: Larry',
            'after' => '[Name]: Toby',
            'type' => 'line',
        ],
        [
            'description' => 'Added the "field" → "Nested field 2"',
            'before' => null,
            'after' => 'bar',
            'type' => 'line',
        ],
    ], $updateAction->changes());
});

test('multi field list actions are formatted correctly', function () {
    enableAllActions();

    $user = auth()->user() ?? createUser();
    $mapping = $this->createMappingWithField($user, FieldType::MULTI(), ['list' => true, 'fields' => [
        [
            'id' => 'nestedFieldId',
            'name' => 'Nested field',
            'type' => FieldType::LINE()->key,
            'options' => ['labeled' => ['freeText' => true]],
        ],
        [
            'id' => 'nestedFieldId2',
            'name' => 'Nested field 2',
            'type' => FieldType::SELECT()->key,
            'options' => ['valueOptions' => ['foo', 'bar', 'baz']],
        ],
    ]]);

    $item = createItem($mapping, $this->formatExpectedValue([
        'fieldId' => ['listValue' => [
            ['fieldValue' => ['nestedFieldId' => ['fieldValue' => 'Larry', 'label' => 'Name']]],
            ['fieldValue' => ['nestedFieldId' => ['fieldValue' => 'Toby', 'label' => 'Name']]],
        ]],
    ]));
    $item->wasRecentlyCreated = false;

    $item->update($this->formatExpectedValue([
        'data' => ['fieldId' => ['listValue' => [
            ['fieldValue' => [
                'nestedFieldId' => ['fieldValue' => 'Larry', 'label' => 'Name'],
            ]],
            ['fieldValue' => [
                'nestedFieldId' => ['fieldValue' => 'Larry', 'label' => 'Name'],
                'nestedFieldId2' => ['fieldValue' => 1],
            ]],
        ]]],
    ]));

    $actions = $item->actions;
    expect($actions)->toHaveCount(2);

    /** @var \App\Models\Action $createAction */
    $createAction = $actions->last();
    $updateAction = $actions->first();
    expect($createAction->description(false))->toBe('Item "Item" created');
    static::assertSame([
        [
            'description' => 'Added the 1st "field" → "Nested field"',
            'before' => null,
            'after' => '[Name]: Larry',
            'type' => 'line',
        ],
        [
            'description' => 'Added the 2nd "field" → "Nested field"',
            'before' => null,
            'after' => '[Name]: Toby',
            'type' => 'line',
        ],
    ], $createAction->changes());

    expect($updateAction->description(false))->toBe('Item "Item" updated');
    static::assertSame([
        [
            'description' => 'Changed the 2nd "field" → "Nested field"',
            'before' => '[Name]: Toby',
            'after' => '[Name]: Larry',
            'type' => 'line',
        ],
        [
            'description' => 'Added the 2nd "field" → "Nested field 2"',
            'before' => null,
            'after' => 'bar',
            'type' => 'line',
        ],
    ], $updateAction->changes());
});

test('multi field labeled actions are formatted correctly', function () {
    enableAllActions();

    $user = auth()->user() ?? createUser();
    $mapping = $this->createMappingWithField($user, FieldType::MULTI(), [
        'labeled' => ['labels' => [
            'a' => 'foo',
            'b' => 'bar',
        ]],
        'list' => ['max' => 5],
        'fields' => [
            [
                'id' => 'nestedFieldId',
                'name' => 'Nested field',
                'type' => FieldType::LINE()->key,
                'options' => ['labeled' => ['freeText' => true]],
            ],
        ],
    ]);

    /** @var \Mappings\Models\Item $item */
    $item = createItem($mapping, $this->formatExpectedValue([
        'fieldId' => ['listValue' => [[
            'label' => 'a',
            'fieldValue' => ['nestedFieldId' => ['fieldValue' => 'Toby', 'label' => 'Name']],
        ]]],
    ]));
    $item->wasRecentlyCreated = false;

    $item->update($this->formatExpectedValue([
        'data' => ['fieldId' => ['listValue' => [[
            'label' => 'b',
            'fieldValue' => [
                'nestedFieldId' => [
                    'label' => 'Name',
                    'fieldValue' => 'Larry',
                ],
            ],
        ]]]],
    ]));

    $actions = $item->actions;
    expect($actions)->toHaveCount(2);

    /** @var \App\Models\Action $createAction */
    $createAction = $actions->last();
    $updateAction = $actions->first();
    expect($createAction->description(false))->toBe('Item "Item" created');
    static::assertSame([
        [
            'description' => 'Added the 1st "field" label',
            'before' => null,
            'after' => '[foo]',
            'type' => 'line',
        ],
        [
            'description' => 'Added the 1st "field" → "Nested field"',
            'before' => null,
            'after' => '[Name]: Toby',
            'type' => 'line',
        ],
    ], $createAction->changes());

    expect($updateAction->description(false))->toBe('Item "Item" updated');
    static::assertSame([
        [
            'description' => 'Changed the 1st "field" label',
            'before' => '[foo]',
            'after' => '[bar]',
            'type' => 'line',
        ],
        [
            'description' => 'Changed the 1st "field" → "Nested field"',
            'before' => '[Name]: Toby',
            'after' => '[Name]: Larry',
            'type' => 'line',
        ],
    ], $updateAction->changes());
});

test('multi field labeled list actions are formatted correctly', function () {
    enableAllActions();

    $user = auth()->user() ?? createUser();
    $mapping = $this->createMappingWithField($user, FieldType::MULTI(), [
        'labeled' => ['freeText' => true],
        'list' => true,
        'fields' => [
            [
                'id' => 'nestedFieldId',
                'name' => 'Nested field',
                'type' => FieldType::LINE()->key,
                'options' => ['labeled' => ['freeText' => true]],
            ],
            [
                'id' => 'nestedFieldId2',
                'name' => 'Nested field 2',
                'type' => FieldType::SELECT()->key,
                'options' => ['valueOptions' => ['foo', 'bar', 'baz']],
            ],
        ],
    ]);

    $item = createItem($mapping, $this->formatExpectedValue([
        'fieldId' => ['listValue' => [[
            'label' => 'Test',
            'fieldValue' => ['nestedFieldId' => ['fieldValue' => 'Toby', 'label' => 'Name']],
        ]]],
    ]));
    $item->wasRecentlyCreated = false;

    $item->update($this->formatExpectedValue([
        'data' => ['fieldId' => ['listValue' => [[
            'label' => 'Test 2',
            'fieldValue' => [
                'nestedFieldId' => ['fieldValue' => 'Larry', 'label' => 'Name'],
                'nestedFieldId2' => ['fieldValue' => 1],
            ],
        ]]]],
    ]));

    $actions = $item->actions;
    expect($actions)->toHaveCount(2);

    /** @var \App\Models\Action $createAction */
    $createAction = $actions->last();
    $updateAction = $actions->first();
    expect($createAction->description(false))->toBe('Item "Item" created');
    static::assertSame([
        [
            'description' => 'Added the 1st "field" label',
            'before' => null,
            'after' => '[Test]',
            'type' => 'line',
        ],
        [
            'description' => 'Added the 1st "field" → "Nested field"',
            'before' => null,
            'after' => '[Name]: Toby',
            'type' => 'line',
        ],
    ], $createAction->changes());

    expect($updateAction->description(false))->toBe('Item "Item" updated');
    static::assertSame([
        [
            'description' => 'Changed the 1st "field" label',
            'before' => '[Test]',
            'after' => '[Test 2]',
            'type' => 'line',
        ],
        [
            'description' => 'Changed the 1st "field" → "Nested field"',
            'before' => '[Name]: Toby',
            'after' => '[Name]: Larry',
            'type' => 'line',
        ],
        [
            'description' => 'Added the 1st "field" → "Nested field 2"',
            'before' => null,
            'after' => 'bar',
            'type' => 'line',
        ],
    ], $updateAction->changes());
});

test('items cannot be sorted by multi fields', function () {
    $this->assertFieldIsNotSortable(FieldType::MULTI(), ['fields' => [['id' => 'nestedFieldId', 'type' => FieldType::LINE()->key]]]);
});
