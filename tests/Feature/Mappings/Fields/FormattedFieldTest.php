<?php

declare(strict_types=1);

use MarkupUtils\MarkupType;
use Tests\Concerns\TestsFields;
use Mappings\Core\Mappings\Fields\FieldType;
use Mappings\Core\Mappings\Fields\Types\FormattedField;

uses(TestsFields::class);

test('a mapping can have a formatted type field', function () {
    $this->assertFieldCreated(FieldType::FORMATTED());
});

test('a formatted field can be saved on an item', function () {
    $this->assertItemCreatedWithField(
        FieldType::FORMATTED(),
        [],
        ['fieldValue' => json_encode(['ops' => [['insert' => 'Larry']]], \JSON_THROW_ON_ERROR)]
    );
});

test('a formatted field can be updated on an item', function () {
    $this->assertItemUpdatedWithField(
        FieldType::FORMATTED(),
        [],
        ['fieldValue' => json_encode(['ops' => [['insert' => 'Larry']]], \JSON_THROW_ON_ERROR)],
        ['fieldValue' => json_encode(['ops' => [['insert' => 'Toby']]], \JSON_THROW_ON_ERROR)],
    );
});

test('a formatted field is not required by default', function () {
    $this->assertValidFieldRequest(FieldType::FORMATTED(), [], ['name' => ['fieldValue' => 'Larry']], ['field' => null]);
});

test('a formatted field can be made required', function () {
    $this->assertInvalidFieldRequest(
        FieldType::FORMATTED(),
        ['rules' => ['required' => true]],
        ['name' => ['fieldValue' => 'Larry']],
        ['input.data.field.fieldValue' => ['The "field" field is required.']],
    );
});

test('a formatted field has a maximum value of 5000 by default', function () {
    $this->assertInvalidFieldRequest(
        FieldType::FORMATTED(),
        [],
        ['field' => ['fieldValue' => '<p>'.str_repeat('a', FormattedField::MAX_LENGTH + 1).'</p>']],
        ['input.data.field.fieldValue' => ['The "field" must not be greater than 5000 characters.']]
    );
});

test('the formatted field can be customized to have a max less than 5000 characters', function () {
    $this->assertInvalidFieldRequest(
        FieldType::FORMATTED(),
        ['rules' => ['max' => 300]],
        ['field' => ['fieldValue' => '<p>'.str_repeat('a', 300 + 1).'</p>']],
        ['input.data.field.fieldValue' => ['The "field" must not be greater than 300 characters.']]
    );
});

test('the formatted field cannot have a max greater than 5000', function () {
    $this->assertInvalidAddFieldRequest(
        FieldType::FORMATTED(),
        ['rules' => ['max' => FormattedField::MAX_LENGTH + 1]],
        ['input.options.rules.max' => ['The max rule must not be greater than 5000.']],
    );
});

test('the formatted field can have a max character limit', function () {
    $user = createUser();
    $mapping = $this->createMappingWithField($user, FieldType::FORMATTED(), ['rules' => ['maxText' => 100]]);

    $shortDelta = ['ops' => [['insert' => str_repeat('a', 99)]]];
    $longDelta = ['ops' => [['insert' => str_repeat('a', 101)]]];

    $this->be($user)->sendCreateItemRequest($mapping, ['field' => ['fieldValue' => json_encode($shortDelta, \JSON_THROW_ON_ERROR)]])
        ->assertJson(['data' => ['items' => ['items' => [
            'createItem' => [
                'item' => [
                    'data' => ['field' => ['fieldValue' => json_encode($shortDelta, \JSON_THROW_ON_ERROR)]],
                ],
            ],
        ]]]], true);

    $this->withGraphQLExceptionHandling();
    $this->sendCreateItemRequest($mapping, ['field' => ['fieldValue' => json_encode($longDelta, \JSON_THROW_ON_ERROR)]])
        ->assertJson(['errors' => [['extensions' => ['validation' => [
            'input.data.field.fieldValue' => ['The "field" must not be greater than 100 characters.'],
        ]]]]]);
});

test('the formatted field cannot have a max text greater than the max rule', function () {
    $this->assertInvalidAddFieldRequest(
        FieldType::FORMATTED(),
        ['rules' => ['maxText' => 6000]],
        ['input.options.rules.maxText' => ['The max text rule must be less than or equal 5000.']],
    );
});

test('malicious tags are removed', function () {
    config(['mappings.fields.formatted.format' => MarkupType::HTML]);
    $this->assertItemCreatedWithField(
        FieldType::FORMATTED(),
        [],
        ['fieldValue' => '<script>Evil code</script><p>Larry</p>'],
        ['fieldValue' => '<p>Larry</p>'],
        ['_v' => '<script>Evil code</script><p>Larry</p>'],
    );
});

test('a formatted field can be returned as plain text and truncated', function () {
    $this->fetchItemRequest(
        FieldType::FORMATTED(),
        [],
        ['fieldValue' => json_encode(['ops' => [
            ['insert' => 'Larry'],
            ['insert' => "\n"],
            ['insert' => 'Toby'],
        ]], \JSON_THROW_ON_ERROR)],
        'data {
            plaintext: field(plaintext: true) { fieldValue }
            truncated: field(truncate: 3) { fieldValue }
            truncatedWithSuffix: field(truncate: 3, suffix: "... cont.") { fieldValue }
            notTruncated: field(truncate: 100) { fieldValue }
        }',
        ['data' => [
            'plaintext' => ['fieldValue' => "Larry\nToby"],
            'truncated' => ['fieldValue' => 'Lar...'],
            'truncatedWithSuffix' => ['fieldValue' => 'Lar... cont.'],
            'notTruncated' => ['fieldValue' => "Larry\nToby"],
        ]]
    );
});

test('formatted field actions are formatted correctly', function () {
    $this->assertItemUpdateCreatedActions(
        FieldType::FORMATTED(),
        [],
        ['fieldValue' => json_encode(['ops' => [['insert' => 'Larry']]], \JSON_THROW_ON_ERROR)],
        ['fieldValue' => json_encode(['ops' => [['insert' => 'Toby']]], \JSON_THROW_ON_ERROR)],
        ['after' => 'Larry'],
        ['before' => 'Larry', 'after' => 'Toby'],
    );
});
