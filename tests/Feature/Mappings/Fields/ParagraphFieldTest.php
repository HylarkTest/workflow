<?php

declare(strict_types=1);

use Tests\Concerns\TestsFields;
use Mappings\Core\Mappings\Fields\FieldType;
use Mappings\Core\Mappings\Fields\Types\ParagraphField;

uses(TestsFields::class);

test('a mapping can have a paragraph type field', function () {
    $this->assertFieldCreated(FieldType::PARAGRAPH());
});

test('a paragraph field can be saved on an item', function () {
    $this->assertItemCreatedWithField(FieldType::PARAGRAPH(), [], ['fieldValue' => 'Larry']);
});

test('a paragraph field can be updated on an item', function () {
    $this->assertItemUpdatedWithField(FieldType::PARAGRAPH(), [], ['fieldValue' => 'Larry'], ['fieldValue' => 'Toby']);
});

test('a paragraph field is not required by default', function () {
    $this->assertValidFieldRequest(FieldType::PARAGRAPH(), [], ['name' => ['fieldValue' => 'Larry']], ['field' => null]);
});

test('a paragraph field can be made required', function () {
    $this->assertInvalidFieldRequest(
        FieldType::PARAGRAPH(),
        ['rules' => ['required' => true]],
        ['name' => ['fieldValue' => 'Larry']],
        ['input.data.field.fieldValue' => ['The "field" field is required.']],
    );
});

test('a paragraph field has a maximum value of 500 by default', function () {
    $this->assertInvalidFieldRequest(
        FieldType::PARAGRAPH(),
        [],
        ['field' => ['fieldValue' => str_repeat('a', ParagraphField::MAX_LENGTH + 1)]],
        ['input.data.field.fieldValue' => ['The "field" must not be greater than 2000 characters.']]
    );
});

test('the paragraph field can be customized to have a max less than 500 characters', function () {
    $this->assertInvalidFieldRequest(
        FieldType::PARAGRAPH(),
        ['rules' => ['max' => 300]],
        ['field' => ['fieldValue' => str_repeat('a', 300 + 1)]],
        ['input.data.field.fieldValue' => ['The "field" must not be greater than 300 characters.']]
    );
});

test('the paragraph field cannot have a max greater than 500', function () {
    $this->assertInvalidAddFieldRequest(
        FieldType::PARAGRAPH(),
        ['rules' => ['max' => ParagraphField::MAX_LENGTH + 1]],
        ['input.options.rules.max' => ['The max rule must not be greater than 2000.']],
    );
});

test('a paragraph field can be truncated', function () {
    $this->fetchItemRequest(
        FieldType::PARAGRAPH(),
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

test('paragraph field actions are formatted correctly', function () {
    $this->assertItemUpdateCreatedActions(
        FieldType::PARAGRAPH(),
        [],
        ['fieldValue' => 'Larry'],
        ['fieldValue' => 'Toby'],
        createChange: ['after' => 'Larry', 'type' => 'paragraph'],
        updateChange: ['after' => 'Toby', 'before' => 'Larry', 'type' => 'paragraph'],
    );
});

test('items can be sorted by paragraph fields', function () {
    $this->assertFieldIsSortable(
        FieldType::PARAGRAPH(),
        [],
        ['AAAA', 'CCCC', 'BBBB', null],
        [1, 2, 0, 3],
        [0, 2, 1, 3],
    );
})->group('es');
