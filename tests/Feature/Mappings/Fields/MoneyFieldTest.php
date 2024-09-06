<?php

declare(strict_types=1);

use Tests\Concerns\TestsFields;
use Mappings\Core\Mappings\Fields\FieldType;

uses(TestsFields::class);

test('a mapping can have a money type field', function () {
    $this->assertFieldCreated(FieldType::MONEY());
});

test('a money field can be saved on an item', function () {
    $this->withGraphQLExceptionHandling();
    $this->assertItemCreatedWithField(
        FieldType::MONEY(),
        [],
        ['fieldValue' => ['currency' => 'GBP', 'amount' => 100]],
        request: 'field { fieldValue { currency amount} }'
    );
});

test('a money field can be updated on an item', function () {
    $this->withGraphQLExceptionHandling();
    $this->assertItemUpdatedWithField(
        FieldType::MONEY(),
        [],
        ['_v' => ['currency' => 'GBP', 'amount' => 100]],
        ['fieldValue' => ['currency' => 'USD', 'amount' => 200]],
        request: 'field { fieldValue { currency amount} }'
    );
});

test('a money field can be made a range', function () {
    $this->assertItemCreatedWithField(
        FieldType::MONEY(),
        ['isRange' => true],
        ['fieldValue' => [
            'currency' => 'GBP',
            'amount' => [
                'from' => 10,
                'to' => 100,
            ],
        ]],
        expectedResponse: ['fieldValue' => ['currency' => 'GBP', 'amount' => ['from' => 10, 'to' => 100]]],
        expectedValue: ['_v' => [10, 100, 'GBP']],
        request: 'field { fieldValue { currency amount { from to } } }'
    );
});

test('a money range field can have just the from set', function () {
    $this->assertItemCreatedWithField(
        FieldType::MONEY(),
        ['isRange' => true],
        ['fieldValue' => [
            'currency' => 'GBP',
            'amount' => [
                'from' => 10,
                'to' => null,
            ],
        ]],
        expectedValue: ['_v' => [10, null, 'GBP']],
        request: 'field { fieldValue { currency amount { from to } } }'
    );
});

test('a money range field can have just the to set', function () {
    $this->assertItemCreatedWithField(
        FieldType::MONEY(),
        ['isRange' => true],
        ['fieldValue' => [
            'currency' => 'GBP',
            'amount' => [
                'from' => null,
                'to' => 10,
            ],
        ]],
        expectedValue: ['_v' => [null, 10, 'GBP']],
        request: 'field { fieldValue { currency amount { from to } } }'
    );
});

test('a money field can be made a range with a specific currency', function () {
    $this->withGraphQLExceptionHandling();
    $this->assertItemUpdatedWithField(
        FieldType::MONEY(),
        ['isRange' => true, 'currency' => 'GBP'],
        null,
        ['fieldValue' => ['amount' => [
            'from' => 12,
            'to' => 13,
        ]]],
        expectedResponse: ['fieldValue' => ['currency' => 'GBP', 'amount' => ['from' => 12, 'to' => 13]]],
        expectedValue: ['_v' => [12, 13]],
        request: 'field { fieldValue { currency amount { from to } } }'
    );
});

test('a money field is not required by default', function () {
    $this->assertValidFieldRequest(
        FieldType::MONEY(),
        [],
        ['name' => ['fieldValue' => 'Larry']],
        ['field' => null],
        request: 'field { fieldValue { currency amount} }'
    );
});

test('a money range field is not required by default', function () {
    $this->assertValidFieldRequest(
        FieldType::MONEY(),
        ['isRange' => true],
        ['name' => ['fieldValue' => 'Larry']],
        ['field' => null],
        request: 'field { fieldValue { currency amount { from to } } }'
    );
});

test('the amount cannot be null', function () {
    $this->assertInvalidFieldRequest(
        FieldType::MONEY(),
        [],
        ['field' => ['fieldValue' => ['currency' => 'GBP', 'amount' => null]]],
        [
            'input.data.field.fieldValue.amount' => ['Add a valid amount.'],
        ],
        request: 'field { fieldValue { currency amount} }'
    );
});

test('the amount range must have to or from', function () {
    $this->assertInvalidFieldRequest(
        FieldType::MONEY(),
        ['isRange' => true],
        ['field' => ['fieldValue' => [
            'currency' => 'GBP',
            'amount' => [
                'from' => null,
                'to' => null,
            ],
        ]]],
        [
            'input.data.field.fieldValue.amount.to' => ['Add at least one valid amount for this range.'],
            'input.data.field.fieldValue.amount.from' => ['Add at least one valid amount for this range.'],
        ],
        request: 'field { fieldValue { currency amount { to from }} }'
    );
});

test('the amount cannot be null in a list', function () {
    $this->assertInvalidFieldRequest(
        FieldType::MONEY(),
        ['list' => true],
        ['field' => ['listValue' => [['fieldValue' => ['currency' => 'GBP']]]]],
        [
            'input.data.field.listValue.0.fieldValue.amount' => ['The amount field is required when "field" is present.'],
        ],
        request: 'field { listValue { fieldValue { currency amount} } }'
    );
});

test('a money field can be made required', function () {
    $this->assertInvalidFieldRequest(
        FieldType::MONEY(),
        ['rules' => ['required' => true]],
        ['name' => ['fieldValue' => 'Larry']],
        ['input.data.field.fieldValue' => ['The "field" field is required.']],
        request: 'field { fieldValue { currency amount} }'
    );
});

test('a money field must have a valid currency', function () {
    $this->assertInvalidFieldRequest(
        FieldType::MONEY(),
        [],
        ['field' => ['fieldValue' => ['currency' => 'ABC', 'amount' => 100]]],
        ['input.data.field.fieldValue.currency' => ['The selected currency is invalid.']],
        request: 'field { fieldValue { currency amount} }'
    );
});

test('the money field can have a fixed currency', function () {
    $this->assertItemCreatedWithField(
        FieldType::MONEY(),
        ['currency' => 'GBP'],
        ['fieldValue' => ['amount' => 100]],
        ['fieldValue' => ['currency' => 'GBP', 'amount' => 100]],
        ['_v' => 100],
        request: 'field { fieldValue { currency amount} }'
    );
});

test('money field actions are formatted correctly', function () {
    $this->assertItemUpdateCreatedActions(
        FieldType::MONEY(),
        [],
        ['fieldValue' => ['currency' => 'GBP', 'amount' => 100]],
        ['fieldValue' => ['currency' => 'USD', 'amount' => 10]],
        createChange: ['after' => '£100'],
        updateChange: ['before' => '£100', 'after' => '$10'],
    );
});

test('range money field actions are formatted correctly', function () {
    $this->assertItemUpdateCreatedActions(
        FieldType::MONEY(),
        ['isRange' => true],
        ['fieldValue' => [10, 100, 'GBP']],
        ['fieldValue' => [18, 20, 'USD']],
        createChange: ['after' => '£10 - £100'],
        updateChange: ['before' => '£10 - £100', 'after' => '$18 - $20'],
    );
});

test('fixed currency money field actions are formatted correctly', function () {
    $this->assertItemUpdateCreatedActions(
        FieldType::MONEY(),
        ['currency' => 'GBP'],
        ['fieldValue' => 100],
        ['fieldValue' => 10],
        createChange: ['after' => '£100'],
        updateChange: ['before' => '£100', 'after' => '£10'],
    );
});

test('fixed currency range money field actions are formatted correctly', function () {
    $this->assertItemUpdateCreatedActions(
        FieldType::MONEY(),
        ['currency' => 'GBP', 'isRange' => true],
        ['fieldValue' => [10, 100]],
        ['fieldValue' => [15, 20]],
        createChange: ['after' => '£10 - £100'],
        updateChange: ['before' => '£10 - £100', 'after' => '£15 - £20'],
    );
});

test('to only range money field actions are formatted correctly', function () {
    $this->assertItemUpdateCreatedActions(
        FieldType::MONEY(),
        ['currency' => 'GBP', 'isRange' => true],
        ['fieldValue' => [10, null]],
        ['fieldValue' => [null, 100]],
        ['after' => '£10+'],
        ['before' => '£10+', 'after' => '<£100'],
    );
});

test('items can be sorted by money fields with fixed currencies', function () {
    $this->assertFieldIsSortable(
        FieldType::MONEY(),
        ['currency' => 'GBP'],
        [10, 30, 20],
    );
})->group('es');

test('items cannot be sorted by money fields with variable currencies', function () {
    $this->assertFieldIsNotSortable(FieldType::MONEY(), ['currency' => null]);
});

test('items cannot be sorted by money fields with range', function () {
    $this->assertFieldIsNotSortable(
        FieldType::MONEY(),
        ['currency' => 'GBP', 'isRange' => true],
    );
});
