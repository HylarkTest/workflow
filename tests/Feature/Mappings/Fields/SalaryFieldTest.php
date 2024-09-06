<?php

declare(strict_types=1);

use Tests\Concerns\TestsFields;
use Mappings\Core\Mappings\Fields\FieldType;

uses(TestsFields::class);

test('a mapping can have a salary', function () {
    $this->assertFieldCreated(FieldType::SALARY());
});

test('a salary field can be saved on an item', function () {
    $this->assertItemCreatedWithField(
        FieldType::SALARY(),
        [],
        ['fieldValue' => ['period' => 'DAILY', 'currency' => 'GBP', 'amount' => 100]],
        request: 'field { fieldValue { period currency amount} }'
    );
});

test('a salary field can be updated on an item', function () {
    $this->withGraphQLExceptionHandling();
    $this->assertItemUpdatedWithField(
        FieldType::SALARY(),
        [],
        ['fieldValue' => ['period' => 'DAILY', 'currency' => 'GBP', 'amount' => 100]],
        ['fieldValue' => ['period' => 'WEEKLY', 'currency' => 'USD', 'amount' => 200]],
        request: 'field { fieldValue { period currency amount} }'
    );
});

test('a salary field can be made a range', function () {
    $this->assertItemCreatedWithField(
        FieldType::SALARY(),
        ['isRange' => true],
        ['fieldValue' => [
            'period' => 'DAILY',
            'currency' => 'GBP',
            'amount' => [
                'from' => 10,
                'to' => 100,
            ],
        ]],
        expectedValue: ['_v' => [10, 100, 'GBP', 'DAILY']],
        request: 'field { fieldValue { period currency amount { from to } } }'
    );
});

test('a salary range field can have just the from set', function () {
    $this->assertItemCreatedWithField(
        FieldType::SALARY(),
        ['isRange' => true],
        ['fieldValue' => [
            'period' => 'DAILY',
            'currency' => 'GBP',
            'amount' => [
                'from' => 10,
                'to' => null,
            ],
        ]],
        expectedValue: ['_v' => [10, null, 'GBP', 'DAILY']],
        request: 'field { fieldValue { period currency amount { from to } } }'
    );
});

test('a salary range field can have just the to set', function () {
    $this->assertItemCreatedWithField(
        FieldType::SALARY(),
        ['isRange' => true],
        ['fieldValue' => [
            'period' => 'DAILY',
            'currency' => 'GBP',
            'amount' => [
                'to' => 10,
                'from' => null,
            ],
        ]],
        expectedValue: ['_v' => [null, 10, 'GBP', 'DAILY']],
        request: 'field { fieldValue { period currency amount { from to } } }'
    );
});

test('the salary amount range must have to or from', function () {
    $this->assertInvalidFieldRequest(
        FieldType::SALARY(),
        ['isRange' => true],
        ['field' => ['fieldValue' => [
            'period' => 'DAILY',
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
        request: 'field { fieldValue { period currency amount { to from }} }'
    );

});

test('a salary field can be made a range with a specific currency', function () {
    $this->assertItemCreatedWithField(
        FieldType::SALARY(),
        ['isRange' => true, 'currency' => 'GBP'],
        ['fieldValue' => [
            'period' => 'DAILY',
            'amount' => [
                'from' => 10,
                'to' => 100,
            ],
        ]],
        expectedResponse: ['fieldValue' => ['period' => 'DAILY', 'currency' => 'GBP', 'amount' => ['from' => 10, 'to' => 100]]],
        expectedValue: ['_v' => [0 => 10, 1 => 100, 3 => 'DAILY']],
        request: 'field { fieldValue { period currency amount { from to } } }'
    );
});

test('a salary field can be made a range with a specific period', function () {
    $this->assertItemCreatedWithField(
        FieldType::SALARY(),
        ['isRange' => true, 'period' => 'MONTHLY'],
        ['fieldValue' => [
            'currency' => 'GBP',
            'amount' => [
                'from' => 10,
                'to' => 100,
            ],
        ]],
        expectedResponse: ['fieldValue' => ['period' => 'MONTHLY', 'currency' => 'GBP', 'amount' => ['from' => 10, 'to' => 100]]],
        expectedValue: ['_v' => [10, 100, 'GBP']],
        request: 'field { fieldValue { period currency amount { from to } } }'
    );
});

test('a salary field is not required by default', function () {
    $this->assertValidFieldRequest(
        FieldType::SALARY(),
        [],
        ['name' => ['fieldValue' => 'Larry']],
        ['field' => null],
        request: 'field { fieldValue { period currency amount} }'
    );
});

test('the salary amount cannot be null', function () {
    $this->assertInvalidFieldRequest(
        FieldType::SALARY(),
        [],
        ['field' => ['fieldValue' => ['currency' => 'GBP', 'amount' => null]]],
        [
            'input.data.field.fieldValue.amount' => ['Add a valid amount.'],
        ],
        request: 'field { fieldValue { period currency amount} }'
    );
});

test('a salary field can be made required', function () {
    $this->assertInvalidFieldRequest(
        FieldType::SALARY(),
        ['rules' => ['required' => true]],
        ['name' => ['fieldValue' => 'Larry']],
        ['input.data.field.fieldValue' => ['The "field" field is required.']],
        request: 'field { fieldValue { period currency amount} }'
    );
});

test('a salary field must have a valid currency', function () {
    $this->assertInvalidFieldRequest(
        FieldType::SALARY(),
        [],
        ['field' => ['fieldValue' => ['currency' => 'ABC', 'amount' => 100]]],
        [
            'input.data.field.fieldValue.currency' => ['The selected currency is invalid.'],
        ],
        request: 'field { fieldValue { period currency amount} }'
    );
});

test('a salary field must have a valid period', function () {
    $this->assertInvalidFieldRequest(
        FieldType::SALARY(),
        [],
        ['field' => ['fieldValue' => ['currency' => 'USD', 'amount' => 100]]],
        [
            'input.data.field.fieldValue.period' => ['The period field is required.'],
        ],
        request: 'field { fieldValue { period currency amount} }'
    );
});

test('the salary field can have a fixed currency', function () {
    $this->assertItemCreatedWithField(
        FieldType::SALARY(),
        ['currency' => 'GBP'],
        ['fieldValue' => ['period' => 'DAILY', 'amount' => 100]],
        ['fieldValue' => ['period' => 'DAILY', 'currency' => 'GBP', 'amount' => 100]],
        ['_v' => ['period' => 'DAILY', 'amount' => 100]],
        request: 'field { fieldValue { period currency amount} }'
    );
});

test('the salary field can have a fixed period', function () {
    $this->assertItemCreatedWithField(
        FieldType::SALARY(),
        ['period' => 'DAILY'],
        ['fieldValue' => ['currency' => 'GBP', 'amount' => 100]],
        ['fieldValue' => ['period' => 'DAILY', 'currency' => 'GBP', 'amount' => 100]],
        ['_v' => ['currency' => 'GBP', 'amount' => 100]],
        request: 'field { fieldValue { period currency amount} }'
    );
});

test('the salary field can have a fixed period and currency', function () {
    $this->assertItemCreatedWithField(
        FieldType::SALARY(),
        ['period' => 'DAILY', 'currency' => 'GBP'],
        ['fieldValue' => ['amount' => 100]],
        ['fieldValue' => ['period' => 'DAILY', 'currency' => 'GBP', 'amount' => 100]],
        ['_v' => 100],
        request: 'field { fieldValue { period currency amount} }'
    );
});

test('salary field actions are formatted correctly', function () {
    $this->assertItemUpdateCreatedActions(
        FieldType::SALARY(),
        [],
        ['fieldValue' => ['period' => 'DAILY', 'currency' => 'GBP', 'amount' => 100]],
        ['fieldValue' => ['period' => 'HOURLY', 'currency' => 'USD', 'amount' => 10]],
        createChange: ['after' => '£100 per day'],
        updateChange: ['before' => '£100 per day', 'after' => '$10 per hour'],
    );
});

test('range salary field actions are formatted correctly', function () {
    $this->assertItemUpdateCreatedActions(
        FieldType::SALARY(),
        ['isRange' => true],
        ['fieldValue' => [10, 100, 'GBP', 'DAILY']],
        ['fieldValue' => [18, 20, 'USD', 'HOURLY']],
        createChange: ['after' => '£10 - £100 per day'],
        updateChange: ['before' => '£10 - £100 per day', 'after' => '$18 - $20 per hour'],
    );
});

test('fixed currency period salary field actions are formatted correctly', function () {
    $this->assertItemUpdateCreatedActions(
        FieldType::SALARY(),
        ['currency' => 'GBP', 'period' => 'DAILY'],
        ['fieldValue' => 100],
        ['fieldValue' => 10],
        createChange: ['after' => '£100 per day'],
        updateChange: ['before' => '£100 per day', 'after' => '£10 per day'],
    );
});

test('fixed currency period range salary field actions are formatted correctly', function () {
    $this->assertItemUpdateCreatedActions(
        FieldType::SALARY(),
        ['isRange' => true, 'currency' => 'GBP', 'period' => 'DAILY'],
        ['fieldValue' => [10, 100]],
        ['fieldValue' => [18, 20]],
        createChange: ['after' => '£10 - £100 per day'],
        updateChange: ['before' => '£10 - £100 per day', 'after' => '£18 - £20 per day'],
    );
});

test('to only range salary field actions are formatted correctly', function () {
    $this->assertItemUpdateCreatedActions(
        FieldType::SALARY(),
        ['currency' => 'GBP', 'period' => 'DAILY', 'isRange' => true],
        ['fieldValue' => [10, null]],
        ['fieldValue' => [null, 100]],
        ['after' => '£10+ per day'],
        ['before' => '£10+ per day', 'after' => '<£100 per day'],
    );
});

test('items can be sorted by salary fields with fixed currencies', function () {
    $this->assertFieldIsSortable(
        FieldType::SALARY(),
        ['currency' => 'GBP', 'period' => 'DAILY'],
        [10, 30, 20],
    );
})->group('es');

test('items cannot be sorted by salary fields with variable currencies or periods', function () {
    $this->assertFieldIsNotSortable(FieldType::SALARY(), ['currency' => null, 'period' => 'DAILY']);
    $this->assertFieldIsNotSortable(FieldType::SALARY(), ['currency' => 'GBP', 'period' => null]);
});

test('items cannot be sorted by salary fields with range', function () {
    $this->assertFieldIsNotSortable(
        FieldType::SALARY(),
        ['currency' => 'GBP', 'period' => 'DAILY', 'isRange' => true],
    );
});
