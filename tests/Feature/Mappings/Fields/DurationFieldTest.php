<?php

declare(strict_types=1);

use Tests\Concerns\TestsFields;
use Mappings\Core\Mappings\Fields\FieldType;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);
uses(TestsFields::class);

test('a mapping can have a duration type field', function () {
    $this->assertFieldCreated(FieldType::DURATION());
});

test('a duration field can be saved on an item', function () {
    $this->assertItemCreatedWithField(
        FieldType::DURATION(),
        [],
        ['fieldValue' => [
            'minutes' => 5,
            'hours' => 6,
            'days' => null,
            'weeks' => 2,
            'months' => null,
        ]],
        null,
        ['_v' => [
            'minutes' => 5,
            'hours' => 6,
            'weeks' => 2,
        ]],
        'field { fieldValue { minutes hours days weeks months } }'
    );
});

test('a duration field can be updated on an item', function () {
    $this->assertItemUpdatedWithField(
        FieldType::DURATION(),
        [],
        ['_v' => [
            'minutes' => 5,
            'hours' => 6,
            'days' => null,
            'weeks' => 2,
            'months' => null,
        ]],
        ['fieldValue' => [
            'minutes' => 1,
            'hours' => 8,
            'days' => null,
            'weeks' => 3,
            'months' => null,
        ]],
        null,
        ['_v' => [
            'minutes' => 1,
            'hours' => 8,
            'weeks' => 3,
        ]],
        'field { fieldValue { minutes hours days weeks months } }'
    );
});

test('a duration field can be made a range', function () {
    $this->assertItemUpdatedWithField(
        FieldType::DURATION(),
        ['isRange' => true],
        ['_v' => [
            ['minutes' => 5],
            ['hours' => 7],
        ]],
        ['fieldValue' => [
            'from' => ['minutes' => 5],
            'to' => ['hours' => 7],
        ]],
        null,
        ['_v' => [
            ['minutes' => 5],
            ['hours' => 7],
        ]],
        'field { fieldValue { from { minutes hours days weeks months } to { minutes hours days weeks months } } }'
    );
});

test('a duration range field can have just the from set', function () {
    $this->assertItemCreatedWithField(
        FieldType::DURATION(),
        ['isRange' => true],
        ['fieldValue' => [
            'from' => ['minutes' => 5],
        ]],
        null,
        ['_v' => [
            ['minutes' => 5],
            null,
        ]],
        'field { fieldValue { from { minutes hours days weeks months } to { minutes hours days weeks months } } }'
    );
});

test('a duration range field can have just the to set', function () {
    $this->assertItemCreatedWithField(
        FieldType::DURATION(),
        ['isRange' => true],
        ['fieldValue' => [
            'to' => ['minutes' => 5],
        ]],
        null,
        ['_v' => [
            null,
            ['minutes' => 5],
        ]],
        'field { fieldValue { from { minutes hours days weeks months } to { minutes hours days weeks months } } }'
    );
});

test('a duration field is not required by default', function () {
    $this->assertValidFieldRequest(
        FieldType::DURATION(),
        [],
        ['name' => ['fieldValue' => 'Larry']],
        ['field' => null],
        'field { fieldValue { minutes hours days weeks months } }'
    );
});

test('a duration field can be made required', function () {
    $this->assertInvalidFieldRequest(
        FieldType::DURATION(),
        ['rules' => ['required' => true]],
        ['name' => ['fieldValue' => 'Larry']],
        ['input.data.field.fieldValue' => ['The "field" field is required.']],
        'field { fieldValue { minutes hours days weeks months } }'
    );
});

test('the to of a duration range field must be above the from', function () {
    $this->assertInvalidFieldRequest(
        FieldType::DURATION(),
        ['isRange' => true, 'rules' => ['enforceGreater' => 'to']],
        ['field' => ['fieldValue' => ['from' => ['hours' => 5], 'to' => ['minutes' => 8]]]],
        ['input.data.field.fieldValue.to' => ['The "field" to must be greater than 5 hours.']],
        'field { fieldValue { from { minutes hours} to { minutes hours } } }'
    );
});

test('duration field actions are formatted correctly', function () {
    $this->assertItemUpdateCreatedActions(
        FieldType::DURATION(),
        [],
        ['fieldValue' => [
            'minutes' => 5,
            'hours' => 6,
            'days' => 0,
            'weeks' => 2,
            'months' => 0,
        ]],
        ['fieldValue' => [
            'minutes' => 1,
            'hours' => 8,
            'days' => 0,
            'weeks' => 3,
            'months' => 0,
        ]],
        ['after' => '2 weeks, 6 hours, and 5 minutes'],
        ['after' => '3 weeks, 8 hours, and 1 minute', 'before' => '2 weeks, 6 hours, and 5 minutes'],
    );
});

test('ranged duration field actions are formatted correctly', function () {
    $this->assertItemUpdateCreatedActions(
        FieldType::DURATION(),
        ['isRange' => true],
        ['fieldValue' => [
            [
                'minutes' => 5,
                'hours' => 6,
                'weeks' => 2,
            ],
            [
                'minutes' => 5,
                'hours' => 6,
                'weeks' => 3,
            ],
        ]],
        ['fieldValue' => [
            [
                'minutes' => 1,
                'hours' => 8,
                'weeks' => 3,
            ],
            [
                'weeks' => 4,
            ],
        ]],
        ['after' => '2 weeks, 6 hours, and 5 minutes - 3 weeks, 6 hours, and 5 minutes'],
        ['after' => '3 weeks, 8 hours, and 1 minute - 4 weeks', 'before' => '2 weeks, 6 hours, and 5 minutes - 3 weeks, 6 hours, and 5 minutes'],
    );
});

test('to only range duration time field actions are formatted correctly', function () {
    $this->assertItemUpdateCreatedActions(
        FieldType::DURATION(),
        ['isRange' => true],
        ['fieldValue' => [
            [
                'minutes' => 5,
                'hours' => 6,
                'weeks' => 2,
            ],
            null,
        ]],
        ['fieldValue' => [
            null,
            [
                'weeks' => 4,
            ],
        ]],
        ['after' => '2 weeks, 6 hours, and 5 minutes+'],
        ['after' => '<4 weeks', 'before' => '2 weeks, 6 hours, and 5 minutes+'],
    );
});

test('items can be sorted by duration', function () {
    $this->assertFieldIsSortable(
        FieldType::DURATION(),
        [],
        [
            ['minutes' => 5, 'hours' => 6],
            ['weeks' => 1],
            ['minutes' => 366],
        ]
    );
})->group('es');

test('items cannot be sorted by durations with range', function () {
    $this->assertFieldIsNotSortable(
        FieldType::DURATION(),
        ['isRange' => true],
    );
});
