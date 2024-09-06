<?php

declare(strict_types=1);

use Tests\Concerns\TestsFields;
use Mappings\Core\Mappings\Fields\FieldType;

uses(TestsFields::class);

test('a mapping can have a currency type field', function () {
    $this->assertFieldCreated(FieldType::CURRENCY());
});

test('a currency field can be saved on an item', function () {
    $this->assertItemCreatedWithField(FieldType::CURRENCY(), [], ['fieldValue' => 'GBP']);
});

test('a currency field can be updated on an item', function () {
    $this->assertItemUpdatedWithField(FieldType::CURRENCY(), [], ['fieldValue' => 'GBP'], ['fieldValue' => 'CAD']);
});

test('a currency field is not required by default', function () {
    $this->assertValidFieldRequest(FieldType::CURRENCY(), [], ['name' => ['fieldValue' => 'Larry']], ['field' => null]);
});

test('a currency field can be made required', function () {
    $this->assertInvalidFieldRequest(
        FieldType::CURRENCY(),
        ['rules' => ['required' => true]],
        ['name' => ['fieldValue' => 'Larry']],
        ['input.data.field.fieldValue' => ['The "field" field is required.']],
    );
});

test('a currency field must be a valid currency', function () {
    $this->assertInvalidFieldRequest(
        FieldType::CURRENCY(),
        [],
        ['field' => ['fieldValue' => 'INV']],
        ['input.data.field.fieldValue' => ['The selected "field" is not a supported currency code.']]
    );
});

test('the currency field can be customized to only allow a subset of currencies', function () {
    $this->assertInvalidFieldRequest(
        FieldType::CURRENCY(),
        ['only' => ['EUR']],
        ['field' => ['fieldValue' => 'GBP']],
        ['input.data.field.fieldValue' => ['The selected "field" is not a supported currency code.']]
    );
});

test('the only option can be empty', function () {
    $this->assertFieldCreated(FieldType::CURRENCY(), ['only' => []]);
});

test('the allowed currencies must be from the supported currencies', function () {
    $this->assertInvalidAddFieldRequest(
        FieldType::CURRENCY(),
        ['only' => ['INV']],
        ['input.options.only' => ['The selected field is invalid.']],
    );
});

test('a currency field can be made the symbol', function () {
    $this->fetchItemRequest(
        FieldType::CURRENCY(),
        [],
        ['_v' => 'GBP'],
        'data {
            field { fieldValue }
            symbol: field(symbol: true) { fieldValue }
        }',
        ['data' => [
            'field' => ['fieldValue' => 'GBP'],
            'symbol' => ['fieldValue' => '£'],
        ]]
    );
});

test('a currency field can be a multi select', function () {
    $this->assertItemCreatedWithField(
        FieldType::CURRENCY(),
        ['multiSelect' => true],
        ['fieldValue' => ['GBP']],
    );
});

test('a currency field can have labels', function () {
    $this->assertItemCreatedWithField(
        FieldType::CURRENCY(),
        [
            'labeled' => ['freeText' => true],
            'multiSelect' => true,
        ],
        ['label' => 'Blah', 'fieldValue' => ['GBP']],
        ['label' => 'Blah', 'fieldValue' => ['GBP']],
        ['label' => 'Blah', 'fieldValue' => ['GBP']],
        'field { label fieldValue }'
    );
});

test('currency field actions are formatted correctly', function () {
    $this->assertItemUpdateCreatedActions(
        FieldType::CURRENCY(),
        [],
        ['fieldValue' => 'GBP'],
        ['fieldValue' => 'USD'],
        ['after' => 'GBP'],
        ['before' => 'GBP', 'after' => 'USD'],
    );
});

test('items can be sorted by currency', function () {
    $this->assertFieldIsSortable(
        FieldType::CURRENCY(),
        [],
        ['EUR', 'USD', 'GBP'],
    );
})->group('es');

test('items cannot be sorted by multi select currency', function () {
    $this->assertFieldIsNotSortable(
        FieldType::CURRENCY(),
        ['multiSelect' => true],
    );
});
