<?php

declare(strict_types=1);

use App\Models\Item;
use App\Models\Mapping;
use Tests\Concerns\TestsFields;
use Mappings\Core\Mappings\Fields\FieldType;

uses(TestsFields::class);

test('a field can be labeled', function () {
    $this->fetchItemRequest(
        FieldType::LINE(),
        ['labeled' => ['freeText' => true]],
        ['label' => 'Name', 'fieldValue' => 'Larry'],
        'data {
            field { label labelKey fieldValue }
        }',
        ['data' => [
            'field' => ['label' => 'Name', 'labelKey' => null, 'fieldValue' => 'Larry'],
        ]]
    );
});

test('the label can be from options', function () {
    $this->fetchItemRequest(
        FieldType::LINE(),
        ['labeled' => ['freeText' => false, 'labels' => ['a' => 'First name', 'b' => 'Last name']]],
        ['label' => 'a', 'fieldValue' => 'Larry'],
        'data {
            field { label labelKey fieldValue }
        }',
        ['data' => [
            'field' => ['label' => 'First name', 'labelKey' => 'a', 'fieldValue' => 'Larry'],
        ]]
    );
});

test('a label field must have options', function () {
    $this->assertInvalidAddFieldRequest(
        FieldType::LINE(),
        ['labeled' => ['freeText' => false, 'labels' => []]],
        ['input.options.labeled.labels' => ['The labels field is required.']],
    );
});

test('the labels must be filled', function () {
    $this->assertInvalidAddFieldRequest(
        FieldType::LINE(),
        ['labeled' => ['freeText' => false, 'labels' => ['a' => '']]],
        ['input.options.labeled.labels.a' => ['Please add at least one label option.']],
    );
});

test('labels are not saved if the label is free text', function () {
    $user = createUser();
    $mapping = $this->createMappingWithField($user, FieldType::LINE(), ['labeled' => ['freeText' => true, 'labels' => ['a' => 'b']]]);
    static::assertArrayNotHasKey('labels', $mapping->fields->last()->option('labeled'));
});

test('labels cannot be too long', function () {
    $this->assertInvalidFieldRequest(
        FieldType::LINE(),
        ['labeled' => ['freeText' => true], 'rules' => ['max' => 300]],
        ['field' => ['label' => str_repeat('a', 100 + 1), 'fieldValue' => str_repeat('a', 300 + 1)]],
        [
            'input.data.field.label' => ['The label must not be greater than 100 characters.'],
            'input.data.field.fieldValue' => ['The "field" must not be greater than 300 characters.'],
        ],
        'field { label labelKey fieldValue }',
    );
});

test('an unlabeled field can become labeled', function () {
    $user = createUser();
    $mapping = $this->createMappingWithField($user, FieldType::LINE(), []);
    $field = $mapping->fields->last();

    $this->be($user)
        ->sendCreateItemRequest($mapping, ['field' => ['fieldValue' => 'Test Value']]);

    $item = $mapping->items->last();

    $base = $user->firstPersonalBase();
    tenancy()->initialize($base);
    $mapping->updateField($field->id, ['options' => ['labeled' => ['freeText' => true]]]);
    $base->unsetRelation('mappings');

    $this->forgetLighthouseClasses();

    $this->graphQl("
        {
            items {
                item(id: \"$item->global_id\") {
                    data { field { fieldValue } }
                }
            }
        }
    ")->assertJson(['data' => ['items' => [
        'item' => ['data' => ['field' => ['fieldValue' => 'Test Value']]],
    ]]], true);
});

test('a labeled field can be unlabeled', function () {
    $user = createUser();
    $mapping = $this->createMappingWithField($user, FieldType::LINE(), ['labeled' => ['freeText' => true]]);
    $field = $mapping->fields->last();

    $this->be($user)
        ->sendCreateItemRequest($mapping, ['field' => ['fieldValue' => 'Test Value']], 'field { fieldValue }');

    $item = $mapping->items->last();

    $base = $user->firstPersonalBase();
    tenancy()->initialize($base);
    $mapping->updateField($field->id, ['options' => []]);
    $base->unsetRelation('mappings');

    $this->graphQl("
        {
            items {
                item(id: \"$item->global_id\") {
                    data { field { fieldValue } }
                }
            }
        }
    ")->assertJson(['data' => ['items' => [
        'item' => ['data' => ['field' => ['fieldValue' => 'Test Value']]],
    ]]], true);
});

test('labeled field actions are formatted correctly', function () {
    $this->assertItemUpdateCreatedActions(
        type: FieldType::LINE(),
        options: ['labeled' => ['freeText' => true]],
        original: ['label' => 'Name', 'fieldValue' => 'Larry'],
        value: ['label' => 'First Name', 'fieldValue' => 'Toby'],
        createChange: ['after' => '[Name]: Larry'],
        updateChange: ['before' => '[Name]: Larry', 'after' => '[First Name]: Toby'],
    );
});

test('option labeled fields have formatted actions', function () {
    $this->assertItemUpdateCreatedActions(
        type: FieldType::LINE(),
        options: ['labeled' => ['freeText' => false, 'labels' => ['a' => 'Name', 'b' => 'First Name']]],
        original: ['label' => 'a', 'fieldValue' => 'Larry'],
        value: ['label' => 'b', 'fieldValue' => 'Toby'],
        createChange: ['after' => '[Name]: Larry'],
        updateChange: ['before' => '[Name]: Larry', 'after' => '[First Name]: Toby'],
    );
});

test('a labeled field that becomes unlabeled has actions formatted correctly', function () {
    $this->assertItemUpdateCreatedActions(
        type: FieldType::LINE(),
        options: ['labeled' => ['freeText' => true]],
        original: ['label' => 'Name', 'fieldValue' => 'Larry'],
        value: ['fieldValue' => 'Toby'],
        createChange: ['after' => '[Name]: Larry'],
        updateChange: ['before' => '[Name]: Larry', 'after' => 'Toby'],
        beforeUpdateCb: function (Item $item, Mapping $mapping) {
            $mapping->updateField('fieldId', ['options' => []]);

            cache()->flush();
            $this->forgetLighthouseClasses();
        }
    );
});

test('an unlabeled field that becomes labeled has actions formatted correctly', function () {
    $this->assertItemUpdateCreatedActions(
        type: FieldType::LINE(),
        original: ['fieldValue' => 'Larry'],
        value: ['label' => 'Name', 'fieldValue' => 'Toby'],
        createChange: ['after' => 'Larry'],
        updateChange: ['before' => 'Larry', 'after' => '[Name]: Toby'],
        beforeUpdateCb: function (Item $item, Mapping $mapping) {
            $mapping->updateField('fieldId', ['options' => ['labeled' => ['freeText' => true]]]);

            cache()->flush();
            $this->forgetLighthouseClasses();
        }
    );
});

test('items can be sorted by labeled fields', function () {
    $this->assertFieldIsSortable(
        FieldType::LINE(),
        ['labeled' => ['freeText' => true]],
        [
            ['_l' => 'label', '_v' => 'AAAA'],
            ['_l' => 'label', '_v' => 'CCCC'],
            ['_l' => 'label', '_v' => 'BBBB'],
        ]
    );
})->group('es');
