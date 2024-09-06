<?php

declare(strict_types=1);

use Tests\Concerns\TestsFields;
use Illuminate\Testing\TestResponse;
use Mappings\Core\Mappings\Fields\FieldType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mappings\Core\Mappings\Fields\AddressFieldName;
use Mappings\Core\Mappings\Fields\Types\AddressField;

uses(RefreshDatabase::class);
uses(TestsFields::class);

test('a mapping can have a address type field', function () {
    $this->assertFieldCreated(FieldType::ADDRESS());
});

test('an address field can be updated', function (): TestResponse {
    $user = createUser();
    $mapping = $this->createMappingWithField($user, FieldType::ADDRESS(), [
        'rules' => ['requiredFields' => [AddressFieldName::LINE1]],
    ]);

    return $this->be($user)->postGraphQL([
        'query' => '
        mutation($input: UpdateMappingFieldInput!) {
            updateMappingField(input: $input) { mapping { id } }
        }
        ',
        'variables' => [
            'input' => [
                'mappingId' => $mapping->globalId(),
                'id' => $mapping->fields->last()->id,
                'name' => 'field',
                'options' => ['rules' => ['requiredFields' => [AddressFieldName::POSTCODE->name]]],
            ],
        ],
    ])->assertJson(['data' => ['updateMappingField' => ['mapping' => ['id' => $mapping->globalId()]]]]);
});

test('an address field can be saved on an item', function () {
    $this->assertItemCreatedWithField(
        FieldType::ADDRESS(),
        [],
        ['fieldValue' => [
            'line1' => '123',
            'line2' => 'Alley Rd',
            'city' => 'New York',
            'state' => 'New York',
            'country' => 'USA',
            'postcode' => 'NY123',
        ]],
        null,
        null,
        'field { fieldValue { line1 line2 city state country postcode } }'
    );
});

test('an address field can be updated on an item', function () {
    $this->assertItemUpdatedWithField(
        FieldType::ADDRESS(),
        [],
        ['fieldValue' => [
            'line1' => '123',
            'line2' => 'Alley Rd',
            'city' => 'New York',
            'state' => 'New York',
            'country' => 'USA',
            'postcode' => 'NY123',
        ]],
        ['fieldValue' => [
            'line1' => '456',
            'line2' => 'Central Park',
            'city' => 'Newer York',
            'state' => 'Newer York',
            'country' => 'United States',
            'postcode' => 'NY456',
        ]],
        null,
        null,
        'field { fieldValue { line1 line2 city state country postcode } }'
    );
});

test('an address field is not required by default', function () {
    $this->assertValidFieldRequest(
        FieldType::ADDRESS(),
        [],
        ['name' => ['fieldValue' => 'Larry']],
        ['field' => null],
        'field { fieldValue { line1 line2 city state country postcode } }'
    );
});

test('an address field can be made required', function () {
    $this->assertInvalidFieldRequest(
        FieldType::ADDRESS(),
        ['rules' => ['required' => true]],
        ['name' => ['fieldValue' => 'Larry']],
        ['input.data.field.fieldValue' => ['The "field" field is required.']],
        'field { fieldValue { line1 line2 city state country postcode } }'
    );
});

test('an address field has a maximum value of 255 by default', function () {
    $longValue = str_repeat('a', AddressField::MAX_LENGTH + 1);
    $this->assertInvalidFieldRequest(
        FieldType::ADDRESS(),
        [],
        ['field' => ['fieldValue' => [
            'line1' => $longValue,
            'line2' => $longValue,
            'city' => $longValue,
            'state' => $longValue,
            'country' => $longValue,
            'postcode' => $longValue,
        ]]],
        [
            'input.data.field.fieldValue.line1' => ['The "field" line1 must not be greater than 255 characters.'],
            'input.data.field.fieldValue.line2' => ['The "field" line2 must not be greater than 255 characters.'],
            'input.data.field.fieldValue.city' => ['The "field" city must not be greater than 255 characters.'],
            'input.data.field.fieldValue.state' => ['The "field" state must not be greater than 255 characters.'],
            'input.data.field.fieldValue.country' => ['The "field" country must not be greater than 255 characters.'],
            'input.data.field.fieldValue.postcode' => ['The "field" postcode must not be greater than 255 characters.'],
        ],
        'field { fieldValue { line1 line2 city state country postcode } }'
    );
});

test('the address field can be customized to have a max less than 255 characters', function () {
    $longValue = str_repeat('a', 100 + 1);
    $this->assertInvalidFieldRequest(
        FieldType::ADDRESS(),
        ['rules' => ['max' => 100]],
        ['field' => ['fieldValue' => [
            'line1' => $longValue,
            'line2' => $longValue,
            'city' => $longValue,
            'state' => $longValue,
            'country' => $longValue,
            'postcode' => $longValue,
        ]]],
        [
            'input.data.field.fieldValue.line1' => ['The "field" line1 must not be greater than 100 characters.'],
            'input.data.field.fieldValue.line2' => ['The "field" line2 must not be greater than 100 characters.'],
            'input.data.field.fieldValue.city' => ['The "field" city must not be greater than 100 characters.'],
            'input.data.field.fieldValue.state' => ['The "field" state must not be greater than 100 characters.'],
            'input.data.field.fieldValue.country' => ['The "field" country must not be greater than 100 characters.'],
            'input.data.field.fieldValue.postcode' => ['The "field" postcode must not be greater than 100 characters.'],
        ],
        'field { fieldValue { line1 line2 city state country postcode } }'
    );
});

test('the address field cannot have a max greater than 255', function () {
    $this->assertInvalidAddFieldRequest(
        FieldType::ADDRESS(),
        ['rules' => ['max' => AddressField::MAX_LENGTH + 1]],
        ['input.options.rules.max' => ['The max rule must not be greater than 255.']],
    );
});

test('specific address fields can be required', function () {
    $this->assertInvalidFieldRequest(
        FieldType::ADDRESS(),
        ['rules' => ['requiredFields' => ['LINE1']]],
        ['field' => ['fieldValue' => ['line2' => 'Street']]],
        ['input.data.field.fieldValue.line1' => ['The "field" line1 field is required when "field" is present.']],
        'field { fieldValue { line1 line2 city state country postcode } }'
    );
});

test('specific address fields are only required if the field is being created', function () {
    $this->assertValidFieldRequest(
        FieldType::ADDRESS(),
        ['rules' => ['requiredFields' => ['LINE1']]],
        [],
        ['field' => null],
        'field { fieldValue { line1 line2 city state country postcode } }'
    );
});

test('required field rules must be valid address types', function () {
    $this->assertInvalidAddFieldRequest(
        FieldType::ADDRESS(),
        ['rules' => ['requiredFields' => ['NOT_A_FIELD']]],
        ['input.options.rules.requiredFields.0' => ['The selected required field is invalid.']],
    );
});

test('the only option must be valid address types', function () {
    $this->assertInvalidAddFieldRequest(
        FieldType::ADDRESS(),
        ['only' => ['NOT_A_FIELD']],
        ['input.options.only.0' => ['The selected field is invalid.']],
    );
});

test('the address field can be labeled', function () {
    $this->assertItemCreatedWithField(
        FieldType::ADDRESS(),
        ['labeled' => ['freeText' => true]],
        ['label' => 'Blah', 'fieldValue' => [
            'line1' => '123',
            'line2' => 'Alley Rd',
            'city' => 'New York',
            'state' => 'New York',
            'country' => 'USA',
            'postcode' => 'NY123',
        ]],
        null,
        null,
        'field { label fieldValue { line1 line2 city state country postcode } }'
    );
});

test('the address field allows nullable values', function () {
    $this->assertItemCreatedWithField(
        FieldType::ADDRESS(),
        [],
        ['fieldValue' => [
            'line1' => '123',
            'line2' => 'Alley Rd',
            'city' => null,
            'state' => null,
            'country' => null,
            'postcode' => null,
        ]],
        null,
        ['fieldValue' => [
            'line1' => '123',
            'line2' => 'Alley Rd',
        ]],
        'field { fieldValue { line1 line2 city state country postcode } }'
    );
});

test('a list address field can be edited after an address is removed', function () {
    $user = createUser();
    $mapping = $this->createMappingWithField(
        $user,
        FieldType::ADDRESS(),
        ['list' => true, 'labeled' => ['freeText' => true]],
    );
    $this->be($user);

    $item = createItem($mapping, [
        'field' => ['_c' => [
            ['_l' => 'Home', '_v' => [
                'line1' => '123',
                'line2' => 'Alley Rd',
                'city' => 'New York',
                'state' => 'New York',
                'country' => 'USA',
                'postcode' => 'NY123',
            ]],
        ]],
    ]);

    $this->sendUpdateItemRequest($item, $mapping, ['field' => ['listValue' => [[
        'label' => null,
        'fieldValue' => [
            'line1' => '',
            'line2' => '',
            'city' => '',
            'state' => '',
            'country' => '',
            'postcode' => '',
        ],
    ]]]], 'field { listValue { fieldValue { line1 } } }')->assertSuccessfulGraphQL();

    $this->forgetLighthouseClasses();

    $this->sendUpdateItemRequest($item, $mapping, [], 'field { listValue { label fieldValue { line1 } } }')
        ->assertSuccessfulGraphQL()
        ->assertJsonPath('data.items.items.updateItem.item.data.field', null);
});

test('address field actions are formatted correctly', function () {
    $this->assertItemUpdateCreatedActions(
        FieldType::ADDRESS(),
        [],
        ['fieldValue' => [
            'line1' => '123',
            'line2' => 'Alley Rd',
            'city' => 'New York',
            'state' => 'New York',
            'country' => 'USA',
            'postcode' => 'NY123',
        ]],
        ['fieldValue' => [
            'line1' => '456',
            'line2' => 'Street Dr',
            'city' => 'London',
            'state' => 'Greater London',
            'country' => 'UK',
            'postcode' => 'LON456',
        ]],
        createChange: [
            'after' => "123\nAlley Rd\nNew York\nNew York\nUSA\nNY123",
            'type' => 'paragraph',
        ],
        updateChange: [
            'before' => "123\nAlley Rd\nNew York\nNew York\nUSA\nNY123",
            'after' => "456\nStreet Dr\nLondon\nGreater London\nUK\nLON456",
            'type' => 'paragraph',
        ],
    );
});

test('items cannot be sorted by address', function () {
    $this->assertFieldIsNotSortable(FieldType::ADDRESS());
});
