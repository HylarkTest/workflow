<?php

declare(strict_types=1);

use Tests\Concerns\TestsFields;
use Mappings\Core\Mappings\Fields\FieldType;

uses(TestsFields::class);

test('a mapping can have a icon type field', function () {
    $this->assertFieldCreated(FieldType::ICON());
});

test('a icon field can be saved on an item', function () {
    $this->assertItemCreatedWithField(FieldType::ICON(), [], ['fieldValue' => 'fa-book']);
});

test('a icon field can be updated on an item', function () {
    $this->assertItemUpdatedWithField(FieldType::ICON(), [], ['fieldValue' => 'fa-book'], ['fieldValue' => 'fa-list']);
});

test('a icon field is not required by default', function () {
    $this->assertValidFieldRequest(FieldType::ICON(), [], ['name' => ['fieldValue' => 'Larry']], ['field' => null]);
});

test('a icon field can be made required', function () {
    $this->assertInvalidFieldRequest(
        FieldType::ICON(),
        ['rules' => ['required' => true]],
        ['name' => ['fieldValue' => 'Larry']],
        ['input.data.field.fieldValue' => ['The "field" field is required.']],
    );
});

test('icon field actions are formatted correctly', function () {
    $this->assertItemUpdateCreatedActions(
        FieldType::ICON(),
        [],
        ['fieldValue' => 'fa-book'],
        ['fieldValue' => 'fa-list'],
    );
});

test('items cannot be sorted by icon fields', function () {
    $this->assertFieldIsNotSortable(FieldType::ICON());
});
