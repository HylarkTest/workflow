<?php

declare(strict_types=1);

use Tests\Concerns\TestsFields;
use Mappings\Core\Mappings\Fields\FieldType;

uses(TestsFields::class);

test('a mapping can have a percentage type field', function () {
    $this->assertFieldCreated(FieldType::PERCENTAGE());
});

test('a percentage field can be saved on an item', function () {
    $this->assertItemCreatedWithField(FieldType::PERCENTAGE(), [], ['fieldValue' => 20]);
});

test('a percentage field can be updated on an item', function () {
    $this->assertItemUpdatedWithField(FieldType::PERCENTAGE(), [], ['fieldValue' => 20], ['fieldValue' => 30]);
});

test('a percentage field is not required by default', function () {
    $this->assertValidFieldRequest(FieldType::PERCENTAGE(), [], ['name' => ['fieldValue' => 'Larry']], ['field' => null]);
});

test('a percentage field can be made required', function () {
    $this->assertInvalidFieldRequest(
        FieldType::PERCENTAGE(),
        ['rules' => ['required' => true]],
        ['name' => ['fieldValue' => 'Larry']],
        ['input.data.field.fieldValue' => ['The "field" field is required.']],
    );
});

test('percentage field actions are formatted correctly', function () {
    $this->assertItemUpdateCreatedActions(
        FieldType::PERCENTAGE(),
        [],
        ['fieldValue' => 20],
        ['fieldValue' => 30],
        createChange: ['after' => '20%'],
        updateChange: ['before' => '20%', 'after' => '30%'],
    );
});

test('items can be sorted by percentage fields', function () {
    $this->assertFieldIsSortable(
        FieldType::PERCENTAGE(),
        [],
        [10, 30, 20]
    );
})->group('es');
