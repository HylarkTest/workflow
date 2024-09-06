<?php

declare(strict_types=1);

use App\Models\Item;
use App\Models\Mapping;
use Tests\Concerns\TestsFields;
use Mappings\Core\Mappings\Fields\FieldType;
use Mappings\Core\Mappings\Fields\Types\LineField;

uses(TestsFields::class);

test('a line field can be saved as a list', function () {
    $this->assertItemCreatedWithField(FieldType::LINE(), ['list' => ['max' => 5]], ['listValue' => [['fieldValue' => 'Larry'], ['fieldValue' => 'Toby']]]);
});

test('list field items can be marked as main', function () {
    $this->assertItemCreatedWithField(
        FieldType::LINE(),
        ['list' => ['max' => 5]],
        requestBody: ['listValue' => [
            ['fieldValue' => 'Larry'],
            ['fieldValue' => 'Toby', 'main' => true],
            ['fieldValue' => 'Greg', 'main' => false],
        ]],
        expectedResponse: ['listValue' => [
            ['fieldValue' => 'Larry', 'main' => false],
            ['fieldValue' => 'Toby', 'main' => true],
            ['fieldValue' => 'Greg', 'main' => false],
        ]],
        expectedValue: ['_c' => [
            ['_v' => 'Larry'],
            ['_v' => 'Toby', '_m' => true],
            ['_v' => 'Greg'],
        ]],
        request: 'field { listValue { fieldValue main } }',
    );
});

test('list field items cannot have more than one main item', function () {
    $this->assertInvalidFieldRequest(
        FieldType::LINE(),
        ['list' => ['max' => 5]],
        ['field' => ['listValue' => [
            ['fieldValue' => 'Larry', 'main' => true],
            ['fieldValue' => 'Toby', 'main' => true],
        ]]],
        ['input.data.field.listValue' => ['Only one item can be marked as main.']],
        'field { listValue { fieldValue main } }'
    );
});

test('a list field cannot have more than the maximum number of items', function () {
    $this->assertInvalidFieldRequest(
        FieldType::LINE(),
        ['list' => ['max' => 5]],
        ['field' => ['listValue' => [
            ['fieldValue' => 'Larry'],
            ['fieldValue' => 'Toby'],
            ['fieldValue' => 'John'],
            ['fieldValue' => 'Gary'],
            ['fieldValue' => 'Barry'],
            ['fieldValue' => 'Perry'],
        ]]],
        ['input.data.field.listValue' => ['The "field" must not have more than 5 items.']],
        'field { listValue { fieldValue } }'
    );
});

test('a list field can be customized to have at least one item', function () {
    $this->assertInvalidFieldRequest(
        FieldType::LINE(),
        ['list' => ['oneRequired' => true]],
        ['field' => ['listValue' => []]],
        ['input.data.field.listValue' => ['The "field" must have at least 1 items.']],
        'field { listValue { fieldValue } }'
    );
});

test('the maximum value applies to lists', function () {
    $this->assertInvalidFieldRequest(
        FieldType::LINE(),
        ['list' => true],
        ['field' => ['listValue' => [['fieldValue' => str_repeat('a', LineField::MAX_LENGTH + 1)]]]],
        ['input.data.field.listValue.0.fieldValue' => ['The "field" must not be greater than 500 characters.']],
        'field { listValue { fieldValue } }'
    );
});

test('a list field cannot become a single field', function () {
    $user = createUser();
    $mapping = $this->createMappingWithField($user, FieldType::LINE(), ['list' => true]);
    $field = $mapping->fields->last();

    $this->be($user)->graphQL('
        mutation($input: UpdateMappingFieldInput!) {
            updateMappingField(input: $input) { code }
        }
        ',
        [
            'input' => [
                'mappingId' => $mapping->globalId(),
                'id' => $field->id(),
                'name' => 'field',
                'options' => ['list' => false],
            ],
        ],
    );

    expect($mapping->fresh()->fields->last()->option('list'))->toBeTrue();
});

test('a single field cannot become a list field', function () {
    $user = createUser();
    $mapping = $this->createMappingWithField($user, FieldType::LINE(), ['list' => false]);
    $field = $mapping->fields->last();

    $this->be($user)->graphQL('
        mutation($input: UpdateMappingFieldInput!) {
            updateMappingField(input: $input) { code }
        }
        ',
        [
            'input' => [
                'mappingId' => $mapping->globalId(),
                'id' => $field->id(),
                'name' => 'field',
                'options' => ['list' => true],
            ],
        ],
    );

    expect($mapping->fresh()->fields->last()->option('list'))->toBeFalse();
});

test('falsey list field values are removed', function () {
    $this->assertItemCreatedWithField(
        FieldType::LINE(),
        ['list' => true],
        ['listValue' => [['fieldValue' => 'abc'], ['fieldValue' => '']]],
        ['listValue' => [['fieldValue' => 'abc']]],
        ['_c' => [['_v' => 'abc']]],
    );
});

test('a multi select list field can have an empty value', function () {
    $this->assertItemCreatedWithField(
        FieldType::SELECT(),
        ['list' => true, 'multiSelect' => true, 'valueOptions' => ['key' => 'option']],
        ['listValue' => [['fieldValue' => ['key']], ['fieldValue' => []]]],
        ['listValue' => [['fieldValue' => [['selectKey' => 'key', 'selectValue' => 'option']]], ['fieldValue' => []]]],
        ['_c' => [['_v' => ['key']], ['_v' => []]]],
        'field { listValue { fieldValue { selectKey selectValue} } }'
    );
});

test('list field actions are formatted correctly', function () {
    $this->assertItemUpdateCreatedActions(
        type: FieldType::LINE(),
        options: ['list' => true],
        original: ['listValue' => [['fieldValue' => 'Larry']]],
        value: ['listValue' => [['fieldValue' => 'Larry'], ['fieldValue' => 'Toby']]],
        createChange: ['after' => 'Larry', 'type' => 'paragraph'],
        updateChange: ['before' => 'Larry', 'after' => "Larry\nToby", 'type' => 'paragraph'],
    );
});

test('labeled list field actions are formatted correctly', function () {
    $this->assertItemUpdateCreatedActions(
        type: FieldType::LINE(),
        options: ['labeled' => ['freeText' => true], 'list' => true],
        original: ['listValue' => [['label' => 'Name', 'fieldValue' => 'Larry']]],
        value: ['listValue' => [['label' => 'Name', 'fieldValue' => 'Larry'], ['label' => 'First Name', 'fieldValue' => 'Toby']]],
        createChange: ['after' => '[Name]: Larry', 'type' => 'paragraph'],
        updateChange: ['before' => '[Name]: Larry', 'after' => "[Name]: Larry\n[First Name]: Toby", 'type' => 'paragraph'],
    );
});

test('only labeled list field actions are formatted correctly', function () {
    $this->assertItemUpdateCreatedActions(
        type: FieldType::LINE(),
        options: ['labeled' => ['freeText' => true], 'list' => true],
        original: ['listValue' => [['label' => 'Name']]],
        value: ['listValue' => [['label' => 'Name'], ['label' => 'First Name']]],
        createChange: ['after' => '[Name]: ', 'type' => 'paragraph'],
        updateChange: ['before' => '[Name]: ', 'after' => "[Name]: \n[First Name]: ", 'type' => 'paragraph'],
    );
});

test('list field items becoming main actions are formatted correctly', function () {
    $this->assertItemUpdateCreatedActions(
        type: FieldType::LINE(),
        options: ['list' => true],
        original: ['listValue' => [['fieldValue' => 'Larry'], ['fieldValue' => 'Toby']]],
        value: ['listValue' => [['fieldValue' => 'Larry', 'main' => true], ['fieldValue' => 'Toby']]],
        createChange: ['after' => "Larry\nToby", 'type' => 'paragraph'],
        updateChange: ['before' => "Larry\nToby", 'after' => "Larry (main)\nToby", 'type' => 'paragraph'],
    );
});

test('list field items removing main actions are formatted correctly', function () {
    $this->assertItemUpdateCreatedActions(
        type: FieldType::LINE(),
        options: ['list' => true],
        original: ['listValue' => [['fieldValue' => 'Larry', 'main' => true]]],
        value: ['listValue' => [['fieldValue' => 'Larry']]],
        createChange: ['after' => 'Larry (main)', 'type' => 'paragraph'],
        updateChange: ['before' => 'Larry (main)', 'after' => 'Larry', 'type' => 'paragraph'],
    );
});

test('a labeled list field that becomes unlabeled has actions formatted correctly', function () {
    $this->assertItemUpdateCreatedActions(
        type: FieldType::LINE(),
        options: ['labeled' => ['freeText' => true], 'list' => true],
        original: ['listValue' => [['label' => 'Name', 'fieldValue' => 'Larry'], ['label' => 'First Name', 'fieldValue' => 'Toby']]],
        value: ['listValue' => [['fieldValue' => 'Larry'], ['fieldValue' => 'Toby']]],
        createChange: ['after' => "[Name]: Larry\n[First Name]: Toby", 'type' => 'paragraph'],
        updateChange: ['before' => "[Name]: Larry\n[First Name]: Toby", 'after' => "Larry\nToby", 'type' => 'paragraph'],
        beforeUpdateCb: function (Item $item, Mapping $mapping) {
            $mapping->updateField('fieldId', ['options' => ['list' => true]]);

            cache()->flush();
            $this->forgetLighthouseClasses();
        }
    );
});

test('an unlabeled list field that becomes labeled has actions formatted correctly', function () {
    $this->assertItemUpdateCreatedActions(
        type: FieldType::LINE(),
        options: ['list' => true],
        original: ['listValue' => [['fieldValue' => 'Larry'], ['fieldValue' => 'Toby']]],
        value: ['listValue' => [['label' => 'Name', 'fieldValue' => 'Larry'], ['label' => 'First Name', 'fieldValue' => 'Toby']]],
        createChange: ['after' => "Larry\nToby", 'type' => 'paragraph'],
        updateChange: ['before' => "Larry\nToby", 'after' => "[Name]: Larry\n[First Name]: Toby", 'type' => 'paragraph'],
        beforeUpdateCb: function (Item $item, Mapping $mapping) {
            $mapping->updateField('fieldId', ['options' => ['labeled' => ['freeText' => true], 'list' => true]]);

            cache()->flush();
            $this->forgetLighthouseClasses();
        }
    );
});

test('items cannot be sorted by list fields', function () {
    $this->assertFieldIsNotSortable(FieldType::LINE(), ['list' => true]);
});
