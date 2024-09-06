<?php

declare(strict_types=1);

use Tests\Concerns\TestsFields;
use Mappings\Core\Mappings\Fields\FieldType;

uses(TestsFields::class);

test('a mapping can have an number type field', function () {
    $this->assertFieldCreated(FieldType::NUMBER());
});

test('an number field can be saved on an item', function () {
    $this->assertItemCreatedWithField(FieldType::NUMBER(), [], ['fieldValue' => 4.3]);
});

test('an number field can be updated on an item', function () {
    $this->assertItemUpdatedWithField(FieldType::NUMBER(), [], ['fieldValue' => 9.2], ['fieldValue' => 3]);
});

test('an number field can be made a range', function () {
    $this->withGraphQLExceptionHandling();
    $this->assertItemUpdatedWithField(
        FieldType::NUMBER(),
        ['isRange' => true],
        ['_v' => [2, 10.9]],
        ['fieldValue' => ['from' => 2.1, 'to' => 10]],
        null,
        ['_v' => [2.1, 10]],
        'field { fieldValue { from to } }'
    );
});

test('a range field can have just the from set', function () {
    $this->assertItemCreatedWithField(
        FieldType::NUMBER(),
        ['isRange' => true],
        ['fieldValue' => ['from' => 2.1]],
        null,
        ['_v' => [2.1, null]],
        'field { fieldValue { from to } }'
    );
});

test('a range field can have just the to set', function () {
    $this->assertItemCreatedWithField(
        FieldType::NUMBER(),
        ['isRange' => true],
        ['fieldValue' => ['to' => 10]],
        null,
        ['_v' => [null, 10]],
        'field { fieldValue { from to } }'
    );
});

test('an number field is not required by default', function () {
    $this->assertValidFieldRequest(FieldType::NUMBER(), [], ['name' => ['fieldValue' => 'Larry']], ['field' => null]);
});

test('an number field can be made required', function () {
    $this->assertInvalidFieldRequest(
        FieldType::DATE(),
        ['rules' => ['required' => true]],
        ['name' => ['fieldValue' => 'Larry']],
        ['input.data.field.fieldValue' => ['The "field" field is required.']],
    );
});

test('an number range field can be made required', function () {
    $this->assertInvalidFieldRequest(
        FieldType::DATE(),
        ['isRange' => true, 'rules' => ['required' => true]],
        ['name' => ['fieldValue' => 'Larry']],
        ['input.data.field.fieldValue' => ['The "field" field is required.']],
        'field { fieldValue { from to } }'
    );
});

test('the to of an number range field must be above the from', function () {
    $this->assertInvalidFieldRequest(
        FieldType::NUMBER(),
        ['isRange' => true, 'rules' => ['enforceGreater' => 'to']],
        ['field' => ['fieldValue' => ['from' => 40.6, 'to' => 10]]],
        ['input.data.field.fieldValue.to' => ['The "field" to must be greater than 40.6.']],
        'field { fieldValue { from to } }'
    );
});

test('number field actions are formatted correctly', function () {
    $this->assertItemUpdateCreatedActions(
        FieldType::NUMBER(),
        [],
        ['fieldValue' => 20.2],
        ['fieldValue' => 11],
        ['after' => '20.2'],
        ['before' => '20.2', 'after' => '11'],
    );
});

test('range number field actions are formatted correctly', function () {
    $this->assertItemUpdateCreatedActions(
        FieldType::NUMBER(),
        ['isRange' => true],
        ['fieldValue' => [20.2, 23]],
        ['fieldValue' => [11, 40.3]],
        ['after' => '20.2 - 23'],
        ['before' => '20.2 - 23', 'after' => '11 - 40.3'],
    );
});

test('to only range number field actions are formatted correctly', function () {
    $this->assertItemUpdateCreatedActions(
        FieldType::NUMBER(),
        ['isRange' => true],
        ['fieldValue' => [20.2, null]],
        ['fieldValue' => [null, 40.3]],
        ['after' => '20.2+'],
        ['before' => '20.2+', 'after' => '<40.3'],
    );
});

test('items can be sorted by number fields', function () {
    $this->assertFieldIsSortable(
        FieldType::NUMBER(),
        [],
        [1.1, 3.3, 2.2]
    );
})->group('es');

test('items cannot be sorted by numbers with range', function () {
    $this->assertFieldIsNotSortable(
        FieldType::NUMBER(),
        ['isRange' => true],
    );
});
