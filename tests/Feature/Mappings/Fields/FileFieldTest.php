<?php

declare(strict_types=1);

use App\Models\Document;
use Tests\Concerns\TestsFields;
use Illuminate\Http\Testing\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Mappings\Core\Mappings\Fields\FieldType;
use Mappings\Core\Mappings\Fields\Types\ImageField;
use Mappings\Core\Documents\Contracts\DocumentRepository;

uses(TestsFields::class);

test('a mapping can have a file type field', function () {
    $this->assertFieldCreated(FieldType::FILE());
});

test('a file field can be saved on an item', function () {
    $this->rethrowGraphQLErrors();
    $file = UploadedFile::fake()->create('field.txt');

    $this->assertItemCreatedWithField(
        FieldType::FILE(),
        [],
        ['fieldValue' => $file],
        tenantFn(fn () => ['fieldValue' => [
            'filename' => 'field.txt',
            'size' => 0,
            'extension' => 'txt',
            'url' => Document::query()->latest()->first()->url(),
        ]]),
        tenantFn(fn () => ['_v' => Document::query()->latest()->first()->id()]),
        '
        field { fieldValue {
            filename
            size
            extension
            url
        } }
        '
    );
});

test('a file field can be updated on an item', function () {
    $user = createUser();
    $firstFile = UploadedFile::fake()->create('field.txt');
    $secondFile = UploadedFile::fake()->create('field2.txt');

    $document = resolve(DocumentRepository::class)->store($firstFile);

    $this->be($user)->assertItemUpdatedWithField(
        FieldType::FILE(),
        [],
        ['_v' => $document->id()],
        ['fieldValue' => $secondFile],
        tenantFn(fn () => ['fieldValue' => [
            'filename' => 'field2.txt',
            'size' => 0,
            'extension' => 'txt',
            'url' => Document::query()->latest('id')->first()->url(),
        ]]),
        tenantFn(fn () => ['_v' => Document::query()->latest('id')->first()->id()]),
        '
        field { fieldValue {
            filename
            size
            extension
            url
        } }
        '
    );
});

test('a file field is not required by default', function () {
    $this->assertValidFieldRequest(
        FieldType::FILE(),
        [],
        ['name' => ['fieldValue' => 'Larry']],
        ['field' => null],
        '
        field { fieldValue {
            filename
            size
            extension
            url
        } }
        '
    );
});

test('a file field can be made required', function () {
    $this->assertInvalidFieldRequest(
        FieldType::FILE(),
        ['rules' => ['required' => true]],
        ['name' => ['fieldValue' => 'Larry']],
        ['input.data.field.fieldValue' => ['The "field" field is required.']],
        '
        field { fieldValue {
            filename
            size
            extension
            url
        } }
        '
    );
});

test('a file field has a maximum size of 2 m b by default', function () {
    $file = new File('file.jpg', fopen(base_path('tests/resources/2MB-image.jpg'), 'r'));

    $this->assertInvalidFieldRequest(
        FieldType::FILE(),
        [],
        ['field' => ['fieldValue' => $file]],
        ['input.data.field.fieldValue' => ['The "field" must not be greater than 2000 kilobytes.']],
        '
        field { fieldValue {
            filename
            size
            extension
            url
        } }
        '
    );
});

test('the file field can be customized to have a max less than 2 mb', function () {
    $file = new File('file.jpg', fopen(base_path('tests/resources/1MB-image.jpg'), 'r'));

    $this->assertInvalidFieldRequest(
        FieldType::FILE(),
        ['rules' => ['max' => 1000]],
        ['field' => ['fieldValue' => $file]],
        ['input.data.field.fieldValue' => ['The "field" must not be greater than 1000 kilobytes.']],
        '
        field { fieldValue {
            filename
            size
            extension
            url
        } }
        '
    );
});

test('the file field cannot have a max greater than 2 mb', function () {
    $this->assertInvalidAddFieldRequest(
        FieldType::FILE(),
        ['rules' => ['max' => ImageField::MAX_SIZE + 1]],
        ['input.options.rules.max' => ['The max rule must not be greater than 2000.']],
    );
});

test('the file field can be customized to have certain extensions', function () {
    Storage::fake('files');
    $file = UploadedFile::fake()->create('file.jpg');

    $this->assertInvalidFieldRequest(
        FieldType::FILE(),
        ['rules' => ['extensions' => ['png']]],
        ['field' => ['fieldValue' => $file]],
        ['input.data.field.fieldValue' => ['The "field" must be a file of type: png.']],
        '
        field { fieldValue {
            filename
            size
            extension
            url
        } }
        '
    );
});

test('file field actions are formatted correctly', function () {
    $user = createUser();
    $firstFile = UploadedFile::fake()->create('field.txt');
    $secondFile = UploadedFile::fake()->create('field2.txt');

    $firstDocument = resolve(DocumentRepository::class)->store($firstFile);
    $secondDocument = resolve(DocumentRepository::class)->store($secondFile);

    $this->be($user)->assertItemUpdateCreatedActions(
        FieldType::FILE(),
        [],
        ['fieldValue' => $firstDocument->id()],
        ['fieldValue' => $secondDocument->id()],
        ['after' => 'field.txt'],
        ['before' => 'field.txt', 'after' => 'field2.txt'],
    );
});

test('items cannot be sorted by file fields', function () {
    $this->assertFieldIsNotSortable(FieldType::FILE());
});
