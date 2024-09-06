<?php

declare(strict_types=1);

use App\Models\Page;
use Tests\Concerns\TestsFields;
use App\Core\Mappings\FieldFilterOperator;
use Mappings\Core\Mappings\Fields\FieldType;

uses(TestsFields::class);

test('a mapping can have a select type field', function () {
    $this->assertFieldCreated(FieldType::SELECT(), ['valueOptions' => ['key' => 'value']]);
});

test('a select field can be made multiselect', function () {
    $this->assertFieldCreated(FieldType::SELECT(), ['multiSelect' => true, 'valueOptions' => ['key' => 'value']]);
});

test('a select field can be saved on an item', function () {
    $this->assertItemCreatedWithField(
        FieldType::SELECT(),
        ['valueOptions' => ['key' => 'value']],
        ['fieldValue' => 'key'],
        ['fieldValue' => ['selectKey' => 'key', 'selectValue' => 'value']],
        ['_v' => 'key'],
        'field { fieldValue { selectKey selectValue} }'
    );
});

test('a multi select list field can have an empty value', function () {
    $this->assertItemCreatedWithField(
        FieldType::SELECT(),
        ['list' => true, 'multiSelect' => true, 'valueOptions' => ['key' => 'option']],
        ['listValue' => [['fieldValue' => ['key']], ['fieldValue' => []]]],
        ['listValue' => [['fieldValue' => [['selectKey' => 'key', 'selectValue' => 'option']]], ['fieldValue' => []]]],
        ['_c' => [['_v' => ['key']], ['_v' => []]]],
        'field { listValue { fieldValue { selectKey selectValue} } }'
    );
});

test('a select field can be retrieved', function () {
    $user = auth()->user() ?? createUser();
    $mapping = $this->createMappingWithField(
        $user,
        FieldType::SELECT(),
        ['valueOptions' => ['foo', 'bar'], 'multiSelect' => true],
    );
    $type = $mapping->graphql_type;
    $singleField = $mapping->graphql_single_field;
    $field = $mapping->fields->last();

    $this->be($user)->convertToFileRequest(route('graphql', $mapping->globalId()), [
        'query' => "
        mutation(\$item: {$type}ItemDataInput) {
            items {
                items {
                    create{$type}(input: { data: \$item }) {
                        code
                        {$singleField} {
                            data {
                                field { fieldValue {
                                    selectKey
                                    selectValue
                                } }
                            }
                        }
                    }
                }
            }
        }
        ",
        'variables' => [
            'item' => array_merge(['name' => ['fieldValue' => 'Larray']], ['field' => ['fieldValue' => ['0', '1']]]),
        ],
    ])->assertJson(['data' => ['items' => ['items' => ['createItem' => ['item' => [
        'data' => ['field' => ['fieldValue' => [
            ['selectKey' => '0', 'selectValue' => 'foo'],
            ['selectKey' => '1', 'selectValue' => 'bar'],
        ]]],
    ]]]]]]);
});

test('an select field can be saved as a list', function () {
    $this->assertItemCreatedWithField(
        FieldType::SELECT(),
        ['valueOptions' => ['foo', 'bar'], 'list' => ['to' => 5]],
        ['listValue' => [['fieldValue' => '0'], ['fieldValue' => '1']]],
        ['listValue' => [['fieldValue' => ['selectKey' => '0', 'selectValue' => 'foo']], ['fieldValue' => ['selectKey' => '1', 'selectValue' => 'bar']]]],
        ['_c' => [['_v' => '0'], ['_v' => '1']]],
        'field { listValue { fieldValue { selectValue selectKey} } }'
    );
});

test('a select field can be updated on an item', function () {
    $this->assertItemUpdatedWithField(
        FieldType::SELECT(),
        ['valueOptions' => ['foo', 'bar']],
        ['_v' => '0'],
        ['fieldValue' => '1'],
        ['fieldValue' => ['selectKey' => '1', 'selectValue' => 'bar']],
        ['_v' => '1'],
        'field { fieldValue { selectValue selectKey} }'
    );
});

test('a select field is not required by default', function () {
    $this->assertValidFieldRequest(
        FieldType::SELECT(),
        ['valueOptions' => ['foo']],
        ['name' => ['fieldValue' => 'Larry']],
        ['field' => null],
        'field { fieldValue { selectValue selectKey} }'
    );
});

test('a select field can be made required', function () {
    $this->assertInvalidFieldRequest(
        FieldType::SELECT(),
        ['valueOptions' => ['foo', 'bar'], 'rules' => ['required' => true]],
        ['name' => ['fieldValue' => 'Larry']],
        ['input.data.field.fieldValue' => ['The "field" field is required.']],
        'field { fieldValue { selectKey selectValue} }'
    );
});

test('the select field cannot save values not in options', function () {
    $this->assertInvalidFieldRequest(
        FieldType::SELECT(),
        ['valueOptions' => ['foo', 'bar']],
        ['field' => ['fieldValue' => '2']],
        ['input.data.field.fieldValue' => ['The selected "field" is invalid.']],
        'field { fieldValue { selectKey selectValue} }'
    );
});

test('the select field can be multi select', function () {
    $this->assertItemCreatedWithField(
        FieldType::SELECT(),
        ['valueOptions' => ['foo', 'bar', 'baz'], 'multiSelect' => true],
        ['fieldValue' => ['0', '1']],
        ['fieldValue' => [['selectKey' => '0', 'selectValue' => 'foo'], ['selectKey' => '1', 'selectValue' => 'bar']]],
        ['_v' => ['0', '1']],
        'field { fieldValue { selectKey selectValue} }'
    );
});

test('a select field can have labels', function () {
    $this->assertItemCreatedWithField(
        FieldType::SELECT(),
        [
            'labeled' => ['freeText' => true],
            'multiSelect' => true,
            'valueOptions' => ['foo', 'bar', 'baz'],
        ],
        ['label' => 'Blah', 'fieldValue' => ['0', '1']],
        ['label' => 'Blah', 'fieldValue' => [['selectKey' => '0', 'selectValue' => 'foo'], ['selectKey' => '1', 'selectValue' => 'bar']]],
        ['label' => 'Blah', 'fieldValue' => ['0', '1']],
        'field { label fieldValue { selectKey selectValue } }'
    );
});

test('select fields can be changed to have labels', function () {
    $user = createUser();
    $mapping = createMapping($user, ['name' => 'Items']);

    $this->be($user)->graphQL(/** @lang GraphQL */ '
    mutation CreateMappingField($input: CreateMappingFieldInput!){
        createMappingField(input: $input) {
            mapping { id }
        }
    }
    ', ['input' => [
        'mappingId' => $mapping->global_id,
        'name' => 'Field',
        'type' => 'SELECT',
        'options' => [
            'list' => true,
            'multiSelect' => true,
            'valueOptions' => ['foo', 'bar', 'baz'],
        ],
    ]]);

    tenancy()->initialize($user->firstPersonalBase());
    $field = $mapping->fresh()->fields->last();

    createItem($mapping, [$field->id => [['0', '2'], ['2']]]);

    $this->be($user)->graphQL(/** @lang GraphQL */ '
    mutation UpdateMappingField($input: UpdateMappingFieldInput!){
        updateMappingField(input: $input) {
            mapping { id }
        }
    }
    ', ['input' => [
        'mappingId' => $mapping->global_id,
        'id' => $field->id,
        'name' => $field->name,
        'options' => [
            'list' => true,
            'multiSelect' => true,
            'valueOptions' => ['foo', 'bar', 'baz'],
            'labeled' => ['freeText' => true],
        ],
    ]]);

    $this->be($user)->graphQL(/** @lang GraphQL */ '
    query {
        items {
            items {
                edges {
                    node {
                        id
                        data {
                            field { listValue {
                                label
                                fieldValue {
                                    selectKey
                                    selectValue
                                }
                            }
                        } }
                    }
                }
            }
        }
    }
    ')->assertSuccessfulGraphQL();
});

test('select field actions are formatted correctly', function () {
    $this->assertItemUpdateCreatedActions(
        FieldType::SELECT(),
        ['valueOptions' => ['foo', 'bar', 'baz']],
        ['fieldValue' => '0'],
        ['fieldValue' => '1'],
        ['after' => 'foo'],
        ['before' => 'foo', 'after' => 'bar'],
    );
});

test('multi select select field actions are formatted correctly', function () {
    $this->assertItemUpdateCreatedActions(
        FieldType::SELECT(),
        ['multiSelect' => true, 'valueOptions' => ['foo', 'bar', 'baz']],
        ['fieldValue' => ['0', '1']],
        ['fieldValue' => ['0', '1', '2']],
        ['after' => 'foo, bar'],
        ['before' => 'foo, bar', 'after' => 'foo, bar, baz'],
    );
});

test('labeled multi select select field actions are formatted correctly', function () {
    $this->assertItemUpdateCreatedActions(
        FieldType::SELECT(),
        ['multiSelect' => true, 'labeled' => ['freeText' => true], 'valueOptions' => ['foo', 'bar', 'baz']],
        ['label' => 'Test', 'fieldValue' => ['0', '1']],
        ['label' => 'Test', 'fieldValue' => ['0', '1', '2']],
        ['after' => '[Test]: foo, bar'],
        ['before' => '[Test]: foo, bar', 'after' => '[Test]: foo, bar, baz'],
    );
});

test('list multi select select field actions are formatted correctly', function () {
    $this->assertItemUpdateCreatedActions(
        FieldType::SELECT(),
        ['multiSelect' => true, 'list' => true, 'valueOptions' => ['foo', 'bar', 'baz']],
        ['listValue' => [['fieldValue' => ['0', '1']]]],
        ['listValue' => [['fieldValue' => ['0']], ['fieldValue' => ['1', '2']]]],
        ['after' => 'foo, bar', 'type' => 'paragraph'],
        ['before' => 'foo, bar', 'after' => "foo\nbar, baz", 'type' => 'paragraph'],
    );
});

test('list labeled multi select select field actions are formatted correctly', function () {
    $this->assertItemUpdateCreatedActions(
        FieldType::SELECT(),
        ['multiSelect' => true, 'labeled' => ['freeText' => true], 'list' => true, 'valueOptions' => ['foo', 'bar', 'baz']],
        ['listValue' => [['label' => 'Test', 'fieldValue' => ['0', '1']]]],
        ['listValue' => [['label' => 'Test', 'fieldValue' => ['0']], ['label' => 'Test', 'fieldValue' => ['1', '2']]]],
        ['after' => '[Test]: foo, bar', 'type' => 'paragraph'],
        ['before' => '[Test]: foo, bar', 'after' => "[Test]: foo\n[Test]: bar, baz", 'type' => 'paragraph'],
    );
});

test('items can be sorted by select fields', function () {
    $this->assertFieldIsSortable(
        FieldType::SELECT(),
        ['valueOptions' => ['C', 'B', 'A']],
        [1, 0, null, 2],
        [1, 0, 3, 2],
        [3, 0, 1, 2],
    );
})->group('es');

test('items cannot be sorted by select fields with multiselect', function () {
    $this->assertFieldIsNotSortable(
        FieldType::SELECT(),
        ['multiSelect' => true, 'valueOptions' => ['C', 'B', 'A']],
    );
});

test('a select field option cannot be removed if it is used as a page filter', function () {
    $this->withGraphQLExceptionHandling();
    $user = createUser();
    $mapping = $this->createMappingWithField($user, FieldType::SELECT(), ['valueOptions' => ['a' => 'foo', 'b' => 'bar', 'c' => 'baz']]);
    $field = $mapping->fields->last();

    $page = Page::factory()->create([
        'space_id' => $user->firstSpace(),
        'mapping_id' => $mapping,
        'name' => 'Test page',
        'fieldFilters' => [['fieldId' => $field->id(), 'operator' => FieldFilterOperator::IS, 'match' => 'b']],
    ]);

    $this->be($user)
        ->sendUpdateFieldRequest($mapping, $field->id(), ['valueOptions' => ['a' => 'foo', 'c' => 'baz']], false)
        ->assertGraphQLValidationError('input.options.valueOptions', 'This field is used to filter pages. Please remove it from the pages first. Page(s): "Test page"');
});

test('a select field cannot be deleted if it is used as a page filter', function () {
    $this->withGraphQLExceptionHandling();
    $user = createUser();
    $mapping = $this->createMappingWithField($user, FieldType::SELECT(), ['valueOptions' => ['a' => 'foo', 'b' => 'bar', 'c' => 'baz']]);
    $field = $mapping->fields->last();

    $page = Page::factory()->create([
        'space_id' => $user->firstSpace(),
        'mapping_id' => $mapping,
        'name' => 'Test page',
        'fieldFilters' => [['fieldId' => $field->id(), 'operator' => FieldFilterOperator::IS, 'match' => 'b']],
    ]);

    $this->be($user)
        ->sendDeleteFieldRequest($mapping, $field->id(), false)
        ->assertGraphQLValidationError('input.id', 'This field is used to filter pages. Please remove it from the pages first. Page(s): "Test page"');
});
