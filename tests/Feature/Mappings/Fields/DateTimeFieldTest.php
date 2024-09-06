<?php

declare(strict_types=1);

use Tests\Concerns\TestsFields;
use Mappings\Core\Mappings\Fields\FieldType;

uses(TestsFields::class);

test('a mapping can have a date type field', function () {
    $this->assertFieldCreated(FieldType::DATE_TIME());
});

test('a date field can be saved on an item', function () {
    $this->assertItemCreatedWithField(FieldType::DATE_TIME(), [], ['fieldValue' => '2019-08-05 13:15:48']);
});

test('a date field can be updated on an item', function () {
    $this->assertItemUpdatedWithField(FieldType::DATE_TIME(), [], ['fieldValue' => '2019-08-05 13:15:48'], ['fieldValue' => '2020-12-06 09:20:14']);
});

test('a date field can be made a range', function () {
    $this->assertItemUpdatedWithField(
        FieldType::DATE_TIME(),
        ['isRange' => true],
        ['_v' => ['2019-08-05 13:15:48', '2020-05-06 15:58:06']],
        ['fieldValue' => ['from' => '2020-12-06 09:20:14', 'to' => '2021-01-23 10:12:12']],
        null,
        ['fieldValue' => ['2020-12-06 09:20:14', '2021-01-23 10:12:12']],
        'field { fieldValue { from to } }'
    );
});

test('a date range field can have just the from set', function () {
    $this->assertItemCreatedWithField(
        FieldType::DATE_TIME(),
        ['isRange' => true],
        ['fieldValue' => ['from' => '2020-12-06 09:20:14']],
        null,
        ['_v' => ['2020-12-06 09:20:14', null]],
        'field { fieldValue { from to } }'
    );
});

test('a date range field can have just the to set', function () {
    $this->assertItemCreatedWithField(
        FieldType::DATE_TIME(),
        ['isRange' => true],
        ['fieldValue' => ['to' => '2020-12-06 09:20:14']],
        null,
        ['_v' => [null, '2020-12-06 09:20:14']],
        'field { fieldValue { from to } }'
    );
});

test('a date field is not required by default', function () {
    $this->assertValidFieldRequest(FieldType::DATE_TIME(), [], ['name' => ['fieldValue' => 'Larry']], ['field' => null]);
});

test('a date field can be made required', function () {
    $this->assertInvalidFieldRequest(
        FieldType::DATE_TIME(),
        ['rules' => ['required' => true]],
        ['name' => ['fieldValue' => 'Larry']],
        ['input.data.field.fieldValue' => ['The "field" field is required.']],
    );
});

test('a date range field can be made required', function () {
    $this->assertInvalidFieldRequest(
        FieldType::DATE_TIME(),
        ['isRange' => true, 'rules' => ['required' => true]],
        ['name' => ['fieldValue' => 'Larry']],
        ['input.data.field.fieldValue' => ['The "field" field is required.']],
        'field { fieldValue { from to } }'
    );
});

test('the to of a date range field must be above the from', function () {
    $this->assertInvalidFieldRequest(
        FieldType::DATE_TIME(),
        ['isRange' => true, 'rules' => ['enforceGreater' => 'to']],
        ['field' => ['fieldValue' => ['from' => '2030-12-31 12:00:00', 'to' => '2000-01-01 12:00:00']]],
        ['input.data.field.fieldValue.to' => ['The "field" to must be greater than 2030-12-31 12:00:00.']],
        'field { fieldValue { from to } }'
    );
});

test('the date field can be customized to have a to date', function () {
    $this->assertInvalidFieldRequest(
        FieldType::DATE_TIME(),
        ['rules' => ['before' => '2000-01-01 12:00:00']],
        ['field' => ['fieldValue' => '2000-01-01 12:00:01']],
        ['input.data.field.fieldValue' => ['The "field" must be a date before 2000-01-01 12:00:00.']]
    );
});

test('the date range field can be customized to have a to date', function () {
    $this->assertInvalidFieldRequest(
        FieldType::DATE_TIME(),
        ['isRange' => true, 'rules' => ['before' => '2000-01-01 12:00:00']],
        ['field' => ['fieldValue' => ['from' => '2000-01-01 11:00:00', 'to' => '2000-01-01 12:00:01']]],
        ['input.data.field.fieldValue.to' => ['The "field" to must be a date before 2000-01-01 12:00:00.']],
        'field { fieldValue { from to } }'
    );
});

test('the date field can be customized to have a from date', function () {
    $this->assertInvalidFieldRequest(
        FieldType::DATE_TIME(),
        ['rules' => ['after' => '2000-01-01 12:00:01']],
        ['field' => ['fieldValue' => '2000-01-01 12:00:00']],
        ['input.data.field.fieldValue' => ['The "field" must be a date after 2000-01-01 12:00:01.']]
    );
});

test('the date range field can be customized to have a from date', function () {
    $this->assertInvalidFieldRequest(
        FieldType::DATE_TIME(),
        ['isRange' => true, 'rules' => ['after' => '2000-01-01 12:00:01']],
        ['field' => ['fieldValue' => ['from' => '2000-01-01 12:00:00', 'to' => '2000-01-01 13:00:00']]],
        ['input.data.field.fieldValue.from' => ['The "field" from must be a date after 2000-01-01 12:00:01.']],
        'field { fieldValue { from to } }'
    );
});

test('the date range field can have a maximum difference between the dates', function () {
    $this->assertInvalidFieldRequest(
        FieldType::DATE_TIME(),
        ['isRange' => true, 'rules' => ['maxDifference' => 60]],
        ['field' => ['fieldValue' => ['from' => '2000-01-01 12:00:00', 'to' => '2000-01-01 12:02:00']]],
        ['input.data.field.fieldValue.to' => ['The "field" to must be 1 minute after "field" from.']],
        'field { fieldValue { from to } }'
    );
});

test('the before and after rules must be valid dates', function () {
    $this->assertInvalidAddFieldRequest(
        FieldType::DATE_TIME(),
        ['rules' => ['before' => 'blah blah', 'after' => 'blip blip']],
        [
            'input.options.rules.before' => ['The before rule is not a valid date.'],
            'input.options.rules.after' => ['The after rule is not a valid date.'],
        ],
    );
});

test('the before rule must be after the after rule', function () {
    $this->assertInvalidAddFieldRequest(
        FieldType::DATE_TIME(),
        ['rules' => ['before' => '2000-01-01 12:00:00', 'after' => '2000-01-02 12:00:00']],
        ['input.options.rules.before' => ['The before rule must be a date after after rule.']],
    );
});

test('the use time and is range options must be boolean', function () {
    $this->assertInvalidAddFieldRequest(
        FieldType::DATE_TIME(),
        ['isRange' => 'string'],
        ['input.options.isRange' => ['The isRange field must be true or false.']],
    );
});

test('a date range field can be fetched for a certain timezone', function () {
    $this->fetchItemRequest(
        FieldType::DATE_TIME(),
        [],
        ['fieldValue' => '2000-01-01 12:00:00'],
        'data {
            date: field { fieldValue }
            frenchDate: field(timezone: "Europe/Paris") { fieldValue }
        }',
        ['data' => [
            'date' => ['fieldValue' => '2000-01-01 12:00:00'],
            'frenchDate' => ['fieldValue' => '2000-01-01 13:00:00'],
        ]]
    );
});

test('a date field can be displayed in a number of formats', function () {
    $this->fetchItemRequest(
        FieldType::DATE_TIME(),
        [],
        ['fieldValue' => '2000-01-01 12:00:00'],
        'data {
            date: field { fieldValue }
            atom: field(format: ATOM, timezone: "Europe/Paris") { fieldValue }
            cookie: field(format: COOKIE) { fieldValue }
            iso8601: field(format: ISO8601) { fieldValue }
            rss: field(format: RSS) { fieldValue }
            w3c: field(format: W3C) { fieldValue }
            datetime: field(format: DATETIME) { fieldValue }
        }',
        ['data' => [
            'date' => ['fieldValue' => '2000-01-01 12:00:00'],
            'atom' => ['fieldValue' => '2000-01-01T13:00:00+01:00'],
            'cookie' => ['fieldValue' => 'Saturday, 01-Jan-2000 12:00:00 UTC'],
            'iso8601' => ['fieldValue' => '2000-01-01T12:00:00+0000'],
            'rss' => ['fieldValue' => 'Sat, 01 Jan 2000 12:00:00 +0000'],
            'w3c' => ['fieldValue' => '2000-01-01T12:00:00+00:00'],
            'datetime' => ['fieldValue' => '2000-01-01 12:00:00'],
        ]]
    );
});

test('date time field actions are formatted correctly', function () {
    $this->assertItemUpdateCreatedActions(
        FieldType::DATE_TIME(),
        [],
        ['fieldValue' => '2022-12-24 23:59:59'],
        ['fieldValue' => '2023-07-13 12:00:00'],
        ['after' => '2022-12-24 23:59:59'],
        ['before' => '2022-12-24 23:59:59', 'after' => '2023-07-13 12:00:00'],
    );
});

test('to only range date time field actions are formatted correctly', function () {
    $this->assertItemUpdateCreatedActions(
        FieldType::DATE_TIME(),
        ['isRange' => true],
        ['fieldValue' => ['2019-08-05 22:00:00', null]],
        ['fieldValue' => [null, '2021-05-06 13:20:00']],
        ['after' => '2019-08-05 22:00:00+'],
        ['before' => '2019-08-05 22:00:00+', 'after' => '<2021-05-06 13:20:00'],
    );
});

test('items can be sorted by date times', function () {
    $this->assertFieldIsSortable(
        FieldType::DATE_TIME(),
        [],
        ['2001-11-20 00:00:00', '2023-02-22 14:44:00', '2023-02-22 13:20:00'],
    );
})->group('es');

test('items cannot be sorted by date times with range', function () {
    $this->assertFieldIsNotSortable(
        FieldType::DATE_TIME(),
        ['isRange' => true],
    );
});
