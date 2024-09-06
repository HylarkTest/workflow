<?php

declare(strict_types=1);

use Tests\Concerns\TestsFields;
use Mappings\Core\Mappings\Fields\FieldType;

uses(TestsFields::class);

test('a mapping can have an integer type field', function () {
    $this->assertFieldCreated(FieldType::INTEGER());
});

test('an integer field can be saved on an item', function () {
    $this->assertItemCreatedWithField(FieldType::INTEGER(), [], ['fieldValue' => 4]);
});

test('an integer field can be updated on an item', function () {
    $this->assertItemUpdatedWithField(FieldType::INTEGER(), [], ['fieldValue' => 9], ['fieldValue' => 3]);
});

test('an integer field can be made a range', function () {
    $this->assertItemUpdatedWithField(
        FieldType::INTEGER(),
        ['isRange' => true],
        ['_v' => [2, 10]],
        ['fieldValue' => ['from' => 2, 'to' => 10]],
        null,
        ['_v' => [2, 10]],
        'field { fieldValue { from to } }'
    );
});

test('a range field can have just the from set', function () {
    $this->assertItemCreatedWithField(
        FieldType::INTEGER(),
        ['isRange' => true],
        ['fieldValue' => ['from' => 2]],
        null,
        ['_v' => [2, null]],
        'field { fieldValue { from to } }'
    );
});

test('a range field can have just the to set', function () {
    $this->assertItemCreatedWithField(
        FieldType::INTEGER(),
        ['isRange' => true],
        ['fieldValue' => ['to' => 10]],
        null,
        ['_v' => [null, 10]],
        'field { fieldValue { from to } }'
    );
});

test('an integer field is not required by default', function () {
    $this->assertValidFieldRequest(FieldType::INTEGER(), [], ['name' => ['fieldValue' => 'Larry']], ['field' => null]);
});

test('an integer field can be made required', function () {
    $this->assertInvalidFieldRequest(
        FieldType::DATE(),
        ['rules' => ['required' => true]],
        ['name' => ['fieldValue' => 'Larry']],
        ['input.data.field.fieldValue' => ['The "field" field is required.']],
    );
});

test('an integer range field can be made required', function () {
    $this->assertInvalidFieldRequest(
        FieldType::DATE(),
        ['isRange' => true, 'rules' => ['required' => true]],
        ['name' => ['fieldValue' => 'Larry']],
        ['input.data.field.fieldValue' => ['The "field" field is required.']],
        'field { fieldValue { from to } }'
    );
});

test('the to of an integer range field can be forced to be above the from', function () {
    $this->assertInvalidFieldRequest(
        FieldType::INTEGER(),
        ['isRange' => true, 'rules' => ['enforceGreater' => 'to']],
        ['field' => ['fieldValue' => ['from' => 40, 'to' => 10]]],
        ['input.data.field.fieldValue.to' => ['The "field" to must be greater than 40.']],
        'field { fieldValue { from to } }'
    );
});

test('the from of an integer range field can be forced to be above the to', function () {
    $this->assertInvalidFieldRequest(
        FieldType::INTEGER(),
        ['isRange' => true, 'rules' => ['enforceGreater' => 'from']],
        ['field' => ['fieldValue' => ['from' => 10, 'to' => 40]]],
        ['input.data.field.fieldValue.from' => ['The "field" from must be greater than 40.']],
        'field { fieldValue { from to } }'
    );
});

test('integer field actions are formatted correctly', function () {
    $this->assertItemUpdateCreatedActions(
        FieldType::INTEGER(),
        [],
        ['_v' => 20],
        ['fieldValue' => 11],
        ['after' => '20'],
        ['before' => '20', 'after' => '11'],
    );
});

test('range integer field actions are formatted correctly', function () {
    $this->assertItemUpdateCreatedActions(
        FieldType::INTEGER(),
        ['isRange' => true],
        ['fieldValue' => [20, 23]],
        ['fieldValue' => [11, 40]],
        ['after' => '20 - 23'],
        ['before' => '20 - 23', 'after' => '11 - 40'],
    );
});

test('to only range integer field actions are formatted correctly', function () {
    $this->assertItemUpdateCreatedActions(
        FieldType::INTEGER(),
        ['isRange' => true],
        ['fieldValue' => [20, null]],
        ['fieldValue' => [null, 40]],
        ['after' => '20+'],
        ['before' => '20+', 'after' => '<40'],
    );
});

test('items can be sorted by integer fields', function () {
    $this->assertFieldIsSortable(
        FieldType::INTEGER(),
        [],
        [1, 111, 11]
    );
})->group('es');

test('items cannot be sorted by integers with range', function () {
    $this->assertFieldIsNotSortable(
        FieldType::INTEGER(),
        ['isRange' => true],
    );
});
