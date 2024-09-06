<?php

declare(strict_types=1);

use App\Models\Page;
use Tests\Concerns\TestsFields;
use App\Core\Mappings\FieldFilterOperator;
use Mappings\Core\Mappings\Fields\FieldType;

uses(TestsFields::class);

test('a mapping can have a boolean type field', function () {
    $this->assertFieldCreated(FieldType::BOOLEAN());
});

test('a boolean field can be saved on an item', function () {
    $this->assertItemCreatedWithField(FieldType::BOOLEAN(), [], ['fieldValue' => true]);
});

test('a boolean field can be updated on an item', function () {
    $this->assertItemUpdatedWithField(FieldType::BOOLEAN(), [], ['fieldValue' => true], ['fieldValue' => false]);
});

test('a boolean field is not required by default', function () {
    $this->assertValidFieldRequest(FieldType::BOOLEAN(), [], ['name' => ['fieldValue' => 'Larry']], ['field' => null]);
});

test('a labeled boolean field can be updated on an item', function () {
    $this->assertItemUpdatedWithField(
        FieldType::BOOLEAN(),
        ['labeled' => ['freeText' => true]],
        ['label' => 'Bool', 'fieldValue' => true],
        ['label' => 'Bool', 'fieldValue' => false],
        request: 'field { label fieldValue }',
    );
});

test('a list boolean field removes items', function () {
    $this->assertItemUpdatedWithField(
        FieldType::BOOLEAN(),
        ['list' => true],
        ['listValue' => [['fieldValue' => true], ['fieldValue' => false]]],
        ['listValue' => [['fieldValue' => true]]],
        expectedValue: ['_c' => [['_v' => true]]],
    );
});

test('a boolean field can be made required', function () {
    $this->assertInvalidFieldRequest(
        FieldType::BOOLEAN(),
        ['rules' => ['required' => true]],
        ['name' => ['fieldValue' => 'Larry']],
        ['input.data.field.fieldValue' => ['The "field" field is required.']],
    );
});

test('boolean field actions are formatted correctly', function () {
    $this->assertItemUpdateCreatedActions(
        FieldType::BOOLEAN(),
        [],
        ['fieldValue' => true],
        ['fieldValue' => false],
        ['after' => 'True'],
        ['before' => 'True', 'after' => 'False'],
    );
});

test('items can be sorted by boolean fields', function () {
    $this->assertFieldIsSortable(
        FieldType::BOOLEAN(),
        [],
        [false, true, null],
        [1, 2, 0],
        [0, 2, 1],
    );
})->group('es');

test('a boolean field cannot be deleted if it is used as a page filter', function () {
    $this->withGraphQLExceptionHandling();
    $user = createUser();
    $mapping = $this->createMappingWithField($user, FieldType::BOOLEAN());
    $field = $mapping->fields->last();

    $page = Page::factory()->create([
        'space_id' => $user->firstSpace(),
        'mapping_id' => $mapping,
        'name' => 'Test page',
        'fieldFilters' => [['fieldId' => $field->id(), 'operator' => FieldFilterOperator::IS, 'match' => 'b']],
    ]);

    $this->be($user)
        ->sendDeleteFieldRequest($mapping, $field->id(), false)
        ->assertGraphQLValidationError('input.id', 'This field is used to filter pages. Please remove it from the pages first. Page(s): "Test page"');
});
