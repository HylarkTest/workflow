<?php

declare(strict_types=1);

use App\Models\Item;
use Tests\Concerns\TestsFields;
use Mappings\Core\Mappings\Fields\FieldType;

uses(TestsFields::class);

test('a mapping can have a rating type field', function () {
    $this->assertFieldCreated(FieldType::RATING());
});

test('a rating field can be saved on an item', function () {
    $this->assertItemCreatedWithField(
        FieldType::RATING(),
        [],
        ['fieldValue' => 3.5],
        ['fieldValue' => ['stars' => 3.5, 'max' => 5]],
        ['_v' => 3.5],
        'field { fieldValue { stars max } }'
    );
});

test('a rating field can be updated on an item', function () {
    $this->assertItemUpdatedWithField(
        FieldType::RATING(),
        [],
        ['_v' => 2.2],
        ['fieldValue' => 3.5],
        ['fieldValue' => ['stars' => 3.5, 'max' => 5]],
        ['_v' => 3.5],
        'field { fieldValue { stars max } }'
    );
});

test('a rating field is not required by default', function () {
    $this->assertValidFieldRequest(
        FieldType::RATING(),
        [],
        ['name' => ['fieldValue' => 'Larry']],
        ['field' => null],
        'field { fieldValue { stars max } }'
    );
});

test('a rating field can be made required', function () {
    $this->assertInvalidFieldRequest(
        FieldType::RATING(),
        ['rules' => ['required' => true]],
        ['name' => ['fieldValue' => 'Larry']],
        ['input.data.field.fieldValue' => ['The "field" field is required.']],
        'field { fieldValue { stars max } }'
    );
});

test('a rating field has a maximum value of 5 by default', function () {
    $this->assertInvalidFieldRequest(
        FieldType::RATING(),
        [],
        ['field' => ['fieldValue' => 6]],
        ['input.data.field.fieldValue' => ['The "field" must not be greater than 5.']],
        'field { fieldValue { stars max } }'
    );
});

test('the rating field can be customized to have a max less than 5', function () {
    $this->assertInvalidFieldRequest(
        FieldType::RATING(),
        ['rules' => ['max' => 8]],
        ['field' => ['fieldValue' => 9]],
        ['input.data.field.fieldValue' => ['The "field" must not be greater than 8.']],
        'field { fieldValue { stars max } }'
    );
});

test('the rating field cannot have a max greater than 20', function () {
    $this->assertInvalidAddFieldRequest(
        FieldType::RATING(),
        ['rules' => ['max' => 21]],
        ['input.options.rules.max' => ['The max rule must not be greater than 20.']],
    );
});

test('rating field actions are formatted correctly', function () {
    $this->assertItemUpdateCreatedActions(
        FieldType::RATING(),
        [],
        ['fieldValue' => 3.5],
        ['fieldValue' => 5],
        createChange: ['after' => '3.5/5'],
        updateChange: ['before' => '3.5/5', 'after' => '5/5'],
    );
});

test('items can be sorted by rating fields', function () {
    $this->assertFieldIsSortable(
        FieldType::RATING(),
        [],
        [1.1, 3.3, null, 2.2],
        [1, 3, 0, 2],
        [2, 0, 3, 1],
    );
})->group('es');

test('list rating fields are indexed correctly', function () {
    $this->assertItemCreatedWithField(
        FieldType::RATING(),
        ['list' => true],
        ['listValue' => [['fieldValue' => 3.5]]],
        ['listValue' => [['fieldValue' => ['stars' => 3.5, 'max' => 5]]]],
        ['_c' => [['_v' => 3.5]]],
        'field { listValue { fieldValue { stars max } } }'
    );
    /** @var \App\Models\Item $item */
    $item = Item::latest()->first();
    $item->base->run(function () use ($item) {
        $searchableArray = $item->toSearchableArray();
        expect($searchableArray['keyword_fields'])
            ->toContain(['value' => 3.5, 'field' => 'fieldId']);
    });
});
