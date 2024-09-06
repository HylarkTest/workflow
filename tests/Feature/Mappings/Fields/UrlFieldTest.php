<?php

declare(strict_types=1);

use Tests\Concerns\TestsFields;
use Mappings\Core\Mappings\Fields\FieldType;

uses(TestsFields::class);

test('a mapping can have a url type field', function () {
    $this->assertFieldCreated(FieldType::URL());
});

test('a url field can be saved on an item', function () {
    $this->assertItemCreatedWithField(FieldType::URL(), [], ['fieldValue' => 'https://hylark.com']);
});

test('a url field can be updated on an item', function () {
    $this->assertItemUpdatedWithField(FieldType::URL(), [], ['fieldValue' => 'https://hylark.com'], ['fieldValue' => 'https://stackoverflow.com']);
});

test('a url field is not required by default', function () {
    $this->assertValidFieldRequest(FieldType::URL(), [], ['name' => ['fieldValue' => 'Larry']], ['field' => null]);
});

test('a url field can be made required', function () {
    $this->assertInvalidFieldRequest(
        FieldType::URL(),
        ['rules' => ['required' => true]],
        ['name' => ['fieldValue' => 'Larry']],
        ['input.data.field.fieldValue' => ['The "field" field is required.']],
    );
});

test('a url field has a maximum value of 1000 by default', function () {
    $this->assertInvalidFieldRequest(
        FieldType::URL(),
        [],
        ['field' => ['fieldValue' => str_repeat('a', 1001)]],
        ['input.data.field.fieldValue' => ['The "field" must not be greater than 1000 characters.']]
    );
});

test('url field actions are formatted correctly', function () {
    $this->assertItemUpdateCreatedActions(
        FieldType::URL(),
        [],
        ['fieldValue' => 'https://hylark.com'],
        ['fieldValue' => 'https://stackoverflow.com'],
    );
});

test('items can be sorted by url field', function () {
    $this->assertFieldIsSortable(
        FieldType::URL(),
        [],
        ['https://google.com', 'https://stackoverflow.com', 'https://hylark.com']
    );
})->group('es');
