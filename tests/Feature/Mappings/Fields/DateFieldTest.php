<?php

declare(strict_types=1);

use Tests\Concerns\TestsFields;
use Mappings\Core\Mappings\Fields\FieldType;

uses(TestsFields::class);

test('a mapping can have a date type field', function () {
    $this->assertFieldCreated(FieldType::DATE());
});

test('a date field can be saved on an item', function () {
    $this->assertItemCreatedWithField(FieldType::DATE(), [], ['fieldValue' => '2019-08-05']);
});

test('a date field can be updated on an item', function () {
    $this->assertItemUpdatedWithField(FieldType::DATE(), [], ['fieldValue' => '2019-08-05'], ['fieldValue' => '2020-12-06']);
});

test('a date field can be made a range', function () {
    $this->withGraphQLExceptionHandling();
    $this->assertItemUpdatedWithField(
        FieldType::DATE(),
        ['isRange' => true],
        ['_v' => ['2019-08-05', '2020-05-06']],
        ['fieldValue' => ['from' => '2020-12-06', 'to' => '2021-01-23']],
        null,
        ['_v' => ['2020-12-06', '2021-01-23']],
        'field { fieldValue { from to } }'
    );
});

test('a date range field can have just the from set', function () {
    $this->assertItemUpdatedWithField(
        FieldType::DATE(),
        ['isRange' => true],
        ['_v' => ['2020-12-06', '2021-01-23']],
        ['fieldValue' => ['from' => '2020-12-06', 'to' => null]],
        null,
        ['_v' => ['2020-12-06', null]],
        'field { fieldValue { from to } }'
    );
});

test('a date range field can have just the to set', function () {
    $this->assertItemCreatedWithField(
        FieldType::DATE(),
        ['isRange' => true],
        ['fieldValue' => ['to' => '2020-12-06']],
        null,
        ['_v' => [null, '2020-12-06']],
        'field { fieldValue { from to } }'
    );
});

test('a date field is not required by default', function () {
    $this->assertValidFieldRequest(FieldType::DATE(), [], ['name' => ['fieldValue' => 'Larry']], ['field' => null]);
});

test('a date field can be made required', function () {
    $this->assertInvalidFieldRequest(
        FieldType::DATE(),
        ['rules' => ['required' => true]],
        ['name' => ['fieldValue' => 'Larry']],
        ['input.data.field.fieldValue' => ['The "field" field is required.']],
    );
});

test('a date range field can be made required', function () {
    $this->assertInvalidFieldRequest(
        FieldType::DATE(),
        ['isRange' => true, 'rules' => ['required' => true]],
        ['name' => ['fieldValue' => 'Larry']],
        ['input.data.field.fieldValue' => ['The "field" field is required.']],
        'field { fieldValue { from to } }'
    );
});

test('the to of a date range field must be above the from', function () {
    $this->assertInvalidFieldRequest(
        FieldType::DATE(),
        ['isRange' => true, 'rules' => ['enforceGreater' => 'to']],
        ['field' => ['fieldValue' => ['from' => '2030-12-31', 'to' => '2000-01-01']]],
        ['input.data.field.fieldValue.to' => ['The "field" to must be greater than 2030-12-31 00:00:00.']],
        'field { fieldValue { from to } }'
    );
});

test('the date field can be customized to have a to date', function () {
    $this->assertInvalidFieldRequest(
        FieldType::DATE(),
        ['rules' => ['before' => '2000-01-01']],
        ['field' => ['fieldValue' => '2000-01-01']],
        ['input.data.field.fieldValue' => ['The "field" must be a date before 2000-01-01.']]
    );
});

test('the date range field can be customized to have a to date', function () {
    $this->assertInvalidFieldRequest(
        FieldType::DATE(),
        ['isRange' => true, 'rules' => ['before' => '2000-01-01']],
        ['field' => ['fieldValue' => ['from' => '2000-01-01', 'to' => '2000-01-02']]],
        ['input.data.field.fieldValue.to' => [
            'The "field" to must be a date before 2000-01-01.',
        ]],
        'field { fieldValue { from to } }'
    );
});

test('the date field can be customized to have a from date', function () {
    $this->assertInvalidFieldRequest(
        FieldType::DATE(),
        ['rules' => ['after' => '2000-01-02']],
        ['field' => ['fieldValue' => '2000-01-01']],
        ['input.data.field.fieldValue' => ['The "field" must be a date after 2000-01-02.']]
    );
});

test('the date range field can be customized to have a from date', function () {
    $this->assertInvalidFieldRequest(
        FieldType::DATE(),
        ['isRange' => true, 'rules' => ['after' => '2000-01-01']],
        ['field' => ['fieldValue' => ['from' => '2000-01-01', 'to' => '2000-01-01']]],
        ['input.data.field.fieldValue.from' => ['The "field" from must be a date after 2000-01-01.']],
        'field { fieldValue { from to } }'
    );
});

test('the maximum difference is in days if use time is false', function () {
    $this->assertInvalidFieldRequest(
        FieldType::DATE(),
        ['isRange' => true, 'rules' => ['maxDifference' => 10]],
        ['field' => ['fieldValue' => ['from' => '2000-01-01', 'to' => '2000-01-11']]],
        ['input.data.field.fieldValue.to' => ['The "field" to must be 1 week and 3 days after "field" from.']],
        'field { fieldValue { from to } }'
    );
});

test('the before and after rules must be valid dates', function () {
    $this->assertInvalidAddFieldRequest(
        FieldType::DATE(),
        ['rules' => ['before' => 'blah blah', 'after' => 'blip blip']],
        [
            'input.options.rules.before' => ['The before rule is not a valid date.'],
            'input.options.rules.after' => ['The after rule is not a valid date.'],
        ],
    );
});

test('the before rule must be after the after rule', function () {
    $this->assertInvalidAddFieldRequest(
        FieldType::DATE(),
        ['rules' => ['before' => '2000-01-01', 'after' => '2000-01-02']],
        ['input.options.rules.before' => ['The before rule must be a date after after rule.']],
    );
});

test('date field actions are formatted correctly', function () {
    $this->assertItemUpdateCreatedActions(
        FieldType::DATE(),
        [],
        ['fieldValue' => '2022-12-24'],
        ['fieldValue' => '2023-07-13'],
        ['after' => '2022-12-24'],
        ['before' => '2022-12-24', 'after' => '2023-07-13'],
    );
});

test('ranged date field actions are formatted correctly', function () {
    $this->assertItemUpdateCreatedActions(
        FieldType::DATE(),
        ['isRange' => true],
        ['fieldValue' => ['2019-08-05', '2020-05-06']],
        ['fieldValue' => ['2020-08-05', '2021-05-06']],
        ['after' => '2019-08-05 - 2020-05-06'],
        ['before' => '2019-08-05 - 2020-05-06', 'after' => '2020-08-05 - 2021-05-06'],
    );
});

test('to only range date field actions are formatted correctly', function () {
    $this->assertItemUpdateCreatedActions(
        FieldType::DATE(),
        ['isRange' => true],
        ['fieldValue' => ['2019-08-05', null]],
        ['fieldValue' => [null, '2021-05-06']],
        ['after' => '2019-08-05+'],
        ['before' => '2019-08-05+', 'after' => '<2021-05-06'],
    );
});

test('items can be sorted by date', function () {
    $this->assertFieldIsSortable(
        FieldType::DATE(),
        [],
        ['2001-11-20', '2023-02-22', '2019-06-18'],
    );
})->group('es');

test('items cannot be sorted by dates with range', function () {
    $this->assertFieldIsNotSortable(
        FieldType::DATE(),
        ['isRange' => true],
    );
});
