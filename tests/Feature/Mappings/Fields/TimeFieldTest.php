<?php

declare(strict_types=1);

use Tests\Concerns\TestsFields;
use Mappings\Core\Mappings\Fields\FieldType;

uses(TestsFields::class);

test('a mapping can have a time type field', function () {
    $this->assertFieldCreated(FieldType::TIME());
});

test('a time field can be saved on an item', function () {
    $this->assertItemCreatedWithField(FieldType::TIME(), [], ['fieldValue' => '12:00:00']);
});

test('a time field can be updated on an item', function () {
    $this->assertItemUpdatedWithField(FieldType::TIME(), [], ['fieldValue' => '12:30:00'], ['fieldValue' => '13:30:00']);
});

test('a time field can be made a range', function () {
    $this->assertItemUpdatedWithField(
        FieldType::TIME(),
        ['isRange' => true],
        ['_v' => ['12:30:00', '16:45:00']],
        ['fieldValue' => ['from' => '12:30:00', 'to' => '16:45:00']],
        null,
        ['_v' => ['12:30:00', '16:45:00']],
        'field { fieldValue { from to } }'
    );
});

test('a time range field can have just the from set', function () {
    $this->assertItemCreatedWithField(
        FieldType::TIME(),
        ['isRange' => true],
        ['fieldValue' => ['from' => '09:20:14']],
        null,
        ['_v' => ['09:20:14', null]],
        'field { fieldValue { from to } }'
    );
});

test('a time range field can have just the to set', function () {
    $this->assertItemCreatedWithField(
        FieldType::TIME(),
        ['isRange' => true],
        ['fieldValue' => ['to' => '09:20:14']],
        null,
        ['fieldValue' => [null, '09:20:14']],
        'field { fieldValue { from to } }'
    );
});

test('a time field is not required by default', function () {
    $this->assertValidFieldRequest(FieldType::TIME(), [], ['name' => ['fieldValue' => 'Larry']], ['field' => null]);
});

test('a time field can be made required', function () {
    $this->assertInvalidFieldRequest(
        FieldType::TIME(),
        ['rules' => ['required' => true]],
        ['name' => ['fieldValue' => 'Larry']],
        ['input.data.field.fieldValue' => ['The "field" field is required.']],
    );
});

test('the to of a time range field must be above the from', function () {
    $this->assertInvalidFieldRequest(
        FieldType::TIME(),
        ['isRange' => true, 'rules' => ['enforceGreater' => 'to']],
        ['field' => ['fieldValue' => ['from' => '13:40:00', 'to' => '12:40:00']]],
        ['input.data.field.fieldValue.to' => ['The "field" to must be greater than 13:40:00.']],
        'field { fieldValue { from to } }'
    );
});

test('a time field must be a valid time', function () {
    $this->assertInvalidFieldRequest(
        FieldType::TIME(),
        [],
        ['field' => ['fieldValue' => 'Larry']],
        ['input.data.field.fieldValue' => ['The "field" must be a valid time.']]
    );
});

test('time field actions are formatted correctly', function () {
    $this->assertItemUpdateCreatedActions(
        FieldType::TIME(),
        [],
        ['fieldValue' => '12:00:00'],
        ['fieldValue' => '13:30:00'],
    );
});

test('ranged time field actions are formatted correctly', function () {
    $this->assertItemUpdateCreatedActions(
        FieldType::TIME(),
        ['isRange' => true],
        ['fieldValue' => ['12:30:00', '17:40:00']],
        ['fieldValue' => ['08:02:00', '18:59:00']],
        ['after' => '12:30:00 - 17:40:00'],
        ['after' => '08:02:00 - 18:59:00', 'before' => '12:30:00 - 17:40:00'],
    );
});

test('to only range time field actions are formatted correctly', function () {
    $this->assertItemUpdateCreatedActions(
        FieldType::TIME(),
        ['isRange' => true],
        ['fieldValue' => ['22:00:00', null]],
        ['fieldValue' => [null, '13:20:00']],
        ['after' => '22:00:00+'],
        ['before' => '22:00:00+', 'after' => '<13:20:00'],
    );
});

test('items can be sorted by time fields', function () {
    $this->assertFieldIsSortable(
        FieldType::TIME(),
        [],
        ['12:00:00', '14:00:00', '13:00:00'],
    );
})->group('es');

test('items cannot be sorted by time fields with range', function () {
    $this->assertFieldIsNotSortable(
        FieldType::TIME(),
        ['isRange' => true],
    );
});
