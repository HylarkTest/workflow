<?php

declare(strict_types=1);

use Tests\Concerns\TestsFields;
use Mappings\Core\Mappings\Fields\FieldType;
use Mappings\Core\Mappings\Fields\Types\EmailField;

uses(TestsFields::class);

test('a mapping can have a email type field', function () {
    $this->assertFieldCreated(FieldType::EMAIL());
});

test('a email field can be saved on an item', function () {
    $this->assertItemCreatedWithField(FieldType::EMAIL(), [], ['fieldValue' => 'l@r.ry']);
});

test('a email field can be updated on an item', function () {
    $this->assertItemUpdatedWithField(FieldType::EMAIL(), [], ['_v' => 'l@r.ry'], ['fieldValue' => 't@b.y']);
});

test('a email field is not required by default', function () {
    $this->assertValidFieldRequest(FieldType::EMAIL(), [], ['name' => ['fieldValue' => 'Larry']], ['field' => null]);
});

test('a email field can be made required', function () {
    $this->assertInvalidFieldRequest(
        FieldType::EMAIL(),
        ['rules' => ['required' => true]],
        ['name' => ['fieldValue' => 'Larry']],
        ['input.data.field.fieldValue' => ['The "field" field is required.']],
    );
});

test('a email field has a maximum value of 255', function () {
    $this->assertInvalidFieldRequest(
        FieldType::EMAIL(),
        [],
        ['field' => ['fieldValue' => str_repeat('a', EmailField::MAX_LENGTH + 1).'@example.com']],
        ['input.data.field.fieldValue' => ['The "field" must not be greater than 255 characters.']]
    );
});

test('email field actions are formatted correctly', function () {
    $this->assertItemUpdateCreatedActions(
        FieldType::EMAIL(),
        [],
        ['fieldValue' => 'abc@mail.com'],
        ['fieldValue' => 'def@example.com'],
        ['after' => 'abc@mail.com'],
        ['before' => 'abc@mail.com', 'after' => 'def@example.com'],
    );
});

test('items can be sorted by email', function () {
    $this->assertFieldIsSortable(
        FieldType::EMAIL(),
        [],
        ['a@a.a', 'c@c.c', 'b@b.b'],
    );
})->group('es');
