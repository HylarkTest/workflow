<?php

declare(strict_types=1);

use Tests\Concerns\TestsFields;
use Mappings\Core\Mappings\Fields\FieldType;

uses(TestsFields::class);

test('a mapping can have a phone type field', function () {
    $this->assertFieldCreated(FieldType::PHONE());
});

test('a phone field can be saved on an item', function () {
    $this->assertItemCreatedWithField(FieldType::PHONE(), [], ['fieldValue' => '012345']);
});

test('a phone field can be updated on an item', function () {
    $this->assertItemUpdatedWithField(FieldType::PHONE(), [], ['fieldValue' => '012345'], ['fieldValue' => '543210']);
});

test('a phone field is not required by default', function () {
    $this->assertValidFieldRequest(FieldType::PHONE(), [], ['name' => ['fieldValue' => '012345']], ['field' => null]);
});

test('a phone field can be made required', function () {
    $this->assertInvalidFieldRequest(
        FieldType::PHONE(),
        ['rules' => ['required' => true]],
        ['name' => ['fieldValue' => '012345']],
        ['input.data.field.fieldValue' => ['The "field" field is required.']],
    );
});

test('a phone field has a maximum value of 50 by default', function () {
    $this->assertInvalidFieldRequest(
        FieldType::PHONE(),
        [],
        ['field' => ['fieldValue' => str_repeat('0', 51)]],
        ['input.data.field.fieldValue' => ['The "field" must not be greater than 50 characters.']]
    );
});

test('phone field actions are formatted correctly', function () {
    $this->assertItemUpdateCreatedActions(
        FieldType::PHONE(),
        [],
        ['fieldValue' => '012345'],
        ['fieldValue' => '543210'],
    );
});

test('items can be sorted by phone', function () {
    $this->assertFieldIsSortable(
        FieldType::EMAIL(),
        [],
        ['+423', '333', '222'],
    );
})->group('es');
