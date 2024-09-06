<?php

declare(strict_types=1);

namespace Tests\Feature\Mappings\Fields;

use Tests\Concerns\TestsFields;
use Mappings\Core\Mappings\Fields\FieldType;
use Mappings\Core\Mappings\Fields\Types\LineField;

uses(TestsFields::class);

test('a mapping can have a line type field', function () {
    $this->assertFieldCreated(FieldType::LINE());
});

test('a field is validated with an empty string', function () {
    $this->withGraphQLExceptionHandling();
    $user = createUser();
    $mapping = createMapping($user);

    $this->be($user)->graphQL('
        mutation($input: CreateMappingFieldInput!) {
            createMappingField(input: $input) {
                code
                mapping { id }
            }
        }
        ',
        [
            'input' => [
                'mappingId' => $mapping->globalId(),
                'name' => '',
                'type' => FieldType::LINE(),
                'options' => [],
            ],
        ],
    )->assertJson(['errors' => [['extensions' => ['validation' => ['input.name' => ['The name field must have a value.']]]]]]);
});

test('a line field can be saved on an item', function () {
    $this->assertItemCreatedWithField(FieldType::LINE(), [], ['fieldValue' => 'Larry']);
});

test('a line field can be updated on an item', function () {
    $this->assertItemUpdatedWithField(FieldType::LINE(), [], ['fieldValue' => 'Larry'], ['fieldValue' => 'Toby']);
});

test('a line field is not required by default', function () {
    $this->assertValidFieldRequest(FieldType::LINE(), [], ['name' => ['fieldValue' => 'Larry']], ['field' => null]);
});

test('a line field can be made required', function () {
    $this->assertInvalidFieldRequest(
        FieldType::LINE(),
        ['rules' => ['required' => true]],
        ['name' => ['fieldValue' => 'Larry']],
        ['input.data.field.fieldValue' => ['The "field" field is required.']],
    );
});

test('a line field has a maximum value of 500 by default', function () {
    $this->assertInvalidFieldRequest(
        FieldType::LINE(),
        [],
        ['field' => ['fieldValue' => str_repeat('a', LineField::MAX_LENGTH + 1)]],
        ['input.data.field.fieldValue' => ['The "field" must not be greater than 500 characters.']]
    );
});

test('the line field can be customized to have a max less than 500 characters', function () {
    $this->assertInvalidFieldRequest(
        FieldType::LINE(),
        ['rules' => ['max' => 300]],
        ['field' => ['fieldValue' => str_repeat('a', 300 + 1)]],
        ['input.data.field.fieldValue' => ['The "field" must not be greater than 300 characters.']]
    );
});

test('the line field cannot have a max greater than 500', function () {
    $this->assertInvalidAddFieldRequest(
        FieldType::LINE(),
        ['rules' => ['max' => LineField::MAX_LENGTH + 1]],
        ['input.options.rules.max' => ['The max rule must not be greater than 500.']],
    );
});

test('only valid options are saved', function () {
    $user = createUser();
    $mapping = createMapping($user);

    $this->be($user)->sendAddFieldRequest($mapping, FieldType::LINE(), ['invalid' => true]);

    $field = $mapping->fresh()->fields->last();
    expect($field->type()->is(FieldType::LINE()))->toBeTrue()
        ->and($field->options())->toBeEmpty();
});

test('a line field can be truncated', function () {
    $this->fetchItemRequest(
        FieldType::LINE(),
        [],
        ['fieldValue' => 'Larry'],
        'data {
            truncated: field(truncate: 3) { fieldValue }
            truncatedWithSuffix: field(truncate: 3, suffix: "... cont.") { fieldValue }
            notTruncated: field(truncate: 100) { fieldValue }
        }',
        ['data' => [
            'truncated' => ['fieldValue' => 'Lar...'],
            'truncatedWithSuffix' => ['fieldValue' => 'Lar... cont.'],
            'notTruncated' => ['fieldValue' => 'Larry'],
        ]]
    );
});

test('line field actions are formatted correctly', function () {
    $this->assertItemUpdateCreatedActions(
        FieldType::LINE(),
        [],
        ['fieldValue' => 'Larry'],
        ['fieldValue' => 'Toby'],
    );
});

test('items can be sorted by line fields', function () {
    $this->assertFieldIsSortable(
        FieldType::LINE(),
        [],
        ['AAAA', 'CCCC', 'BBBB', null],
        [1, 2, 0, 3],
        [0, 2, 1, 3],
    );
})->group('es');
