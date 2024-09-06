<?php

declare(strict_types=1);

use Tests\Concerns\TestsFields;
use Mappings\Core\Mappings\Fields\FieldType;

uses(TestsFields::class);

test('a mapping can have a vote type field', function () {
    $this->assertFieldCreated(FieldType::VOTE());
});

test('a vote field can be saved on an item', function () {
    $this->assertItemCreatedWithField(FieldType::VOTE(), [], ['fieldValue' => 1]);
});

test('a vote field can be incremented on an item', function () {
    $this->assertItemUpdatedWithField(
        FieldType::VOTE(),
        [],
        ['_v' => 1],
        ['fieldValue' => 3],
        ['fieldValue' => 4],
        ['_v' => 4],
    );
});

test('a vote field can be decremented on an item', function () {
    $this->assertItemUpdatedWithField(
        FieldType::VOTE(),
        [],
        ['_v' => 3],
        ['fieldValue' => -1],
        ['fieldValue' => 2],
        ['_v' => 2],
    );
});

test('a vote field is not required by default', function () {
    $this->assertValidFieldRequest(FieldType::VOTE(), [], ['name' => ['fieldValue' => 'Larry']], ['field' => null]);
});

test('a vote field can be made required', function () {
    $this->assertInvalidFieldRequest(
        FieldType::VOTE(),
        ['rules' => ['required' => true]],
        ['name' => ['fieldValue' => 'Larry']],
        ['input.data.field.fieldValue' => ['The "field" field is required.']],
    );
});

test('vote field actions are formatted correctly', function () {
    $this->assertItemUpdateCreatedActions(
        FieldType::VOTE(),
        [],
        ['fieldValue' => 3],
        ['fieldValue' => 7],
        ['after' => '3'],
        ['after' => '7', 'before' => '3'],
    );
});

test('items can be sorted by vote field', function () {
    $this->assertFieldIsSortable(
        FieldType::VOTE(),
        [],
        [1, 3, 2]
    );
})->group('es');
