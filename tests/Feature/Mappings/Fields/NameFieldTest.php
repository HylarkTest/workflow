<?php

declare(strict_types=1);

use Tests\Concerns\TestsFields;
use Mappings\Core\Mappings\Fields\FieldType;

uses(TestsFields::class);

test('a mapping can have a name type field', function () {
    $this->assertFieldCreated(FieldType::NAME());
});

test('a name field can be saved on an item', function () {
    $this->assertItemCreatedWithField(FieldType::NAME(), [], ['fieldValue' => 'Larry']);
});

test('a name field can be updated on an item', function () {
    $this->assertItemUpdatedWithField(FieldType::NAME(), [], ['fieldValue' => 'Larry'], ['fieldValue' => 'Toby']);
});

test('a name field is not required by default', function () {
    $this->assertValidFieldRequest(FieldType::NAME(), [], ['name' => ['fieldValue' => 'Larry']], ['field' => null]);
});

test('a name field can be made required', function () {
    $this->assertInvalidFieldRequest(
        FieldType::NAME(),
        ['rules' => ['required' => true]],
        ['name' => ['fieldValue' => 'Larry']],
        ['input.data.field.fieldValue' => ['The "field" field is required.']],
    );
});

test('a name field has a maximum value of 255 by default', function () {
    $this->assertInvalidFieldRequest(
        FieldType::NAME(),
        [],
        ['field' => ['fieldValue' => str_repeat('a', 256)]],
        ['input.data.field.fieldValue' => ['The "field" must not be greater than 255 characters.']]
    );
});

test('name field actions are formatted correctly', function () {
    $this->assertItemUpdateCreatedActions(
        FieldType::NAME(),
        [],
        ['fieldValue' => 'Larry'],
        ['fieldValue' => 'Toby'],
    );
});

test('items can be sorted by name fields', function () {
    $this->assertFieldIsSortable(
        FieldType::NAME(),
        [],
        ['Abe', 'Chloe', 'Betty'],
    );
})->group('es');
