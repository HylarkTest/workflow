<?php

declare(strict_types=1);

use Tests\Concerns\TestsFields;
use Mappings\Core\Mappings\Fields\FieldType;

uses(TestsFields::class);

test('a mapping can have a title type field', function () {
    $this->assertFieldCreated(FieldType::TITLE());
});

test('a title field can be saved on an item', function () {
    $this->assertItemCreatedWithField(FieldType::TITLE(), [], ['fieldValue' => 'Larry']);
});

test('a title field can be updated on an item', function () {
    $this->assertItemUpdatedWithField(FieldType::TITLE(), [], ['fieldValue' => 'Larry'], ['fieldValue' => 'Toby']);
});

test('a title field is not required by default', function () {
    $this->assertValidFieldRequest(FieldType::TITLE(), [], ['name' => ['fieldValue' => 'Larry']], ['field' => null]);
});

test('a title field can be made required', function () {
    $this->assertInvalidFieldRequest(
        FieldType::TITLE(),
        ['rules' => ['required' => true]],
        ['name' => ['fieldValue' => 'Larry']],
        ['input.data.field.fieldValue' => ['The "field" field is required.']],
    );
});

test('a title field has a maximum value of 50 by default', function () {
    $this->assertInvalidFieldRequest(
        FieldType::TITLE(),
        [],
        ['field' => ['fieldValue' => str_repeat('a', 51)]],
        ['input.data.field.fieldValue' => ['The "field" must not be greater than 50 characters.']]
    );
});

test('title field actions are formatted correctly', function () {
    $this->assertItemUpdateCreatedActions(
        FieldType::TITLE(),
        [],
        ['fieldValue' => 'Larry'],
        ['fieldValue' => 'Toby'],
    );
});

test('items cannot be sorted by title field', function () {
    $this->assertFieldIsNotSortable(FieldType::TITLE());
});
