<?php

declare(strict_types=1);

use Mappings\Models\Image;
use Tests\Concerns\TestsFields;
use Illuminate\Http\Testing\File;
use Illuminate\Http\UploadedFile;
use Intervention\Image\ImageManager;
use Illuminate\Support\Facades\Storage;
use Mappings\Core\Mappings\Fields\FieldType;
use Mappings\Core\Mappings\Fields\Types\ImageField;
use Mappings\Core\Documents\Contracts\ImageRepository;

uses(TestsFields::class);

test('a mapping can have an image type field', function () {
    $this->assertFieldCreated(FieldType::IMAGE());
});

test('an image field can be saved on an item', function () {
    $this->rethrowGraphQLErrors();
    Storage::fake('images');
    $image = [
        'image' => UploadedFile::fake()->image('field.jpg'),
        'url' => '',
        'xOffset' => 0,
        'yOffset' => 0,
        'width' => 0,
        'height' => 0,
        'rotate' => 0,
    ];

    $this->assertItemCreatedWithField(
        FieldType::IMAGE(),
        [],
        ['fieldValue' => $image],
        tenantFn(fn () => ['fieldValue' => [
            'filename' => 'field.jpg',
            'size' => 226,
            'extension' => 'jpg',
            'url' => Image::query()->latest()->first()->url(),
        ]]),
        tenantFn(fn () => ['_v' => ['image' => Image::query()->latest()->first()->id()]]),
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

test('a cropped image field can be saved on an item', function () {
    Storage::fake('images');
    $image = UploadedFile::fake()->image('field.jpg');

    $this->assertItemCreatedWithField(
        FieldType::IMAGE(),
        ['croppable' => true],
        ['fieldValue' => [
            'image' => $image,
            'xOffset' => 2,
            'yOffset' => 2,
            'width' => 5,
            'height' => 5,
        ]],
        tenantFn(fn () => ['fieldValue' => [
            'filename' => 'field.jpg',
            'size' => 224,
            'extension' => 'jpg',
            'url' => Image::query()->latest('id')->first()->url(),
            'originalUrl' => Image::query()->latest('id')->take(2)->get()->get(1)->url(),
            'xOffset' => 2,
            'yOffset' => 2,
            'width' => 5,
            'height' => 5,
        ]]),
        tenantFn(fn () => ['_v' => [
            'image' => Image::query()->latest('id')->first()->id(),
            'originalImage' => Image::query()->latest('id')->take(2)->get()->get(1)->id(),
            'xOffset' => 2,
            'yOffset' => 2,
            'width' => 5,
            'height' => 5,
        ]]),
        '
        field { fieldValue {
            filename
            size
            extension
            url
            originalUrl
            xOffset
            yOffset
            width
            height
        } }
        '
    );
});

test('an image field can be updated on an item', function () {
    $user = createUser();
    $firstImage = UploadedFile::fake()->image('field.jpg');
    $secondImage = [
        'image' => UploadedFile::fake()->image('field2.jpg', 11),
        'url' => '',
        'xOffset' => 0,
        'yOffset' => 0,
        'width' => 0,
        'height' => 0,
        'rotate' => 0,
    ];

    $document = resolve(ImageRepository::class)->store($firstImage);

    $this->be($user)->assertItemUpdatedWithField(
        FieldType::IMAGE(),
        [],
        ['_v' => ['image' => $document->id()]],
        ['fieldValue' => $secondImage],
        tenantFn(fn () => ['fieldValue' => [
            'filename' => 'field2.jpg',
            'size' => 226,
            'extension' => 'jpg',
            'url' => Image::query()->latest('id')->first()->url(),
        ]]),
        tenantFn(fn () => ['_v' => ['image' => Image::query()->latest('id')->first()->id()]]),
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

test('a croppable image field can be updated on an item', function () {
    Storage::fake('images');
    $user = createUser();
    $original = UploadedFile::fake()->image('field.jpg');

    $documents = resolve(ImageRepository::class);

    $originalDocument = $documents->store($original);

    $image = (new ImageManager)->make($original)
        ->crop(5, 5, 2, 2)->save();
    $imageFile = new UploadedFile(
        $image->basePath(),
        $original->getClientOriginalName(),
        $original->getClientMimeType(),
        $original->getError(),
    );

    $imageDocument = $documents->store($imageFile);

    $newFile = UploadedFile::fake()->image('field2.jpg', 11);

    Storage::disk('images')->assertExists([
        $originalDocument->url,
        $imageDocument->url,
    ]);

    $this->be($user)->assertItemUpdatedWithField(
        FieldType::IMAGE(),
        ['croppable' => true],
        ['_v' => [
            'image' => $imageDocument->id(),
            'originalImage' => $originalDocument->id(),
            'width' => 5,
            'height' => 5,
            'xOffset' => 2,
            'yOffset' => 2,
        ]],
        ['fieldValue' => [
            'image' => $newFile,
            'width' => 6,
            'height' => 6,
            'xOffset' => 1,
            'yOffset' => 1,
        ]],
        tenantFn(fn () => ['fieldValue' => [
            'filename' => 'field2.jpg',
            'size' => 224,
            'extension' => 'jpg',
            'url' => Image::query()->latest('id')->first()->url(),
            'originalUrl' => Image::query()->latest('id')->take(2)->get()->get(1)->url(),
            'xOffset' => 1,
            'yOffset' => 1,
            'width' => 6,
            'height' => 6,
        ]]),
        tenantFn(fn () => ['_v' => [
            'image' => Image::query()->latest('id')->first()->id(),
            'originalImage' => Image::query()->latest('id')->take(2)->get()->get(1)->id(),
            'width' => 6,
            'height' => 6,
            'xOffset' => 1,
            'yOffset' => 1,
        ]]),
        '
        field { fieldValue {
            filename
            size
            extension
            url
            originalUrl
            width
            height
            xOffset
            yOffset
        } }
        '
    );

    $user->firstPersonalBase()->run(function () use ($originalDocument, $imageDocument) {
        Storage::disk('images')->assertMissing([
            $originalDocument->url(),
            $imageDocument->url(),
        ]);
    });
});

test('a image field is not required by default', function () {
    $this->assertValidFieldRequest(
        FieldType::IMAGE(),
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

test('a image field can be made required', function () {
    $this->assertInvalidFieldRequest(
        FieldType::IMAGE(),
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

test('a croppable image field can be set to required', function () {
    Storage::fake('images');
    $image = UploadedFile::fake()->image('field.jpg');

    $this->assertValidFieldRequest(
        FieldType::IMAGE(),
        ['croppable' => true, 'rules' => ['required' => true]],
        [
            'field' => ['fieldValue' => [
                'image' => $image,
                'width' => 5,
                'height' => 5,
                'xOffset' => 2,
                'yOffset' => 2,
            ]],
        ],
        tenantFn(fn () => [
            'field' => ['fieldValue' => [
                'filename' => 'field.jpg',
                'size' => 224,
                'extension' => 'jpg',
                'url' => Image::query()->latest('id')->first()->url(),
            ]],
        ]),
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

test('an image field has a maximum size of 2 m b by default', function () {
    Storage::fake('images');
    $image = [
        'image' => new File('image.jpg', fopen(__DIR__.'/../../../resources/2MB-image.jpg', 'r')),
        'url' => '',
        'xOffset' => 0,
        'yOffset' => 0,
        'width' => 0,
        'height' => 0,
        'rotate' => 0,
    ];

    $this->assertInvalidFieldRequest(
        FieldType::IMAGE(),
        [],
        ['field' => ['fieldValue' => $image]],
        ['input.data.field.fieldValue.image' => ['The "field" file must not be greater than 2000 kilobytes.']],
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

test('the image field can be customized to have a max less than 2 mb', function () {
    Storage::fake('images');
    $image = [
        'image' => new File('image.jpg', fopen(__DIR__.'/../../../resources/1MB-image.jpg', 'r')),
        'url' => '',
        'xOffset' => 0,
        'yOffset' => 0,
        'width' => 0,
        'height' => 0,
        'rotate' => 0,
    ];

    $this->assertInvalidFieldRequest(
        FieldType::IMAGE(),
        ['rules' => ['max' => 1000]],
        ['field' => ['fieldValue' => $image]],
        ['input.data.field.fieldValue.image' => ['The "field" file must not be greater than 1000 kilobytes.']],
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

test('the image field cannot have a max greater than 2 mb', function () {
    $this->assertInvalidAddFieldRequest(
        FieldType::IMAGE(),
        ['rules' => ['max' => ImageField::MAX_SIZE + 1]],
        ['input.options.rules.max' => ['The max rule must not be greater than 2000.']],
    );
});

test('an image field must have a valid mime type', function () {
    Storage::fake('images');
    $image = [
        'image' => UploadedFile::fake()->create('file.txt'),
        'url' => '',
        'xOffset' => 0,
        'yOffset' => 0,
        'width' => 0,
        'height' => 0,
        'rotate' => 0,
    ];

    $this->assertInvalidFieldRequest(
        FieldType::IMAGE(),
        [],
        ['field' => ['fieldValue' => $image]],
        ['input.data.field.fieldValue.image' => ['The "field" file must be an image.']],
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

test('the image field can be customized to have certain extensions', function () {
    Storage::fake('images');
    $image = [
        'image' => UploadedFile::fake()->image('image.jpg'),
        'url' => '',
        'xOffset' => 0,
        'yOffset' => 0,
        'width' => 0,
        'height' => 0,
        'rotate' => 0,
    ];

    $this->assertInvalidFieldRequest(
        FieldType::IMAGE(),
        ['rules' => ['extensions' => ['png']]],
        ['field' => ['fieldValue' => $image]],
        ['input.data.field.fieldValue.image' => ['The "field" file must be a file of type: png.']],
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

test('the image field can be added with specific extensions', function () {
    $user = createUser();
    $mapping = createMapping($user);
    $this->be($user)->sendAddFieldRequest($mapping, FieldType::IMAGE(), ['rules' => ['extensions' => ['png', 'gif']]])
        ->assertJson(['data' => [
            'createMappingField' => [
                'mapping' => [
                    'id' => $mapping->global_id,
                ],
            ],
        ]]);
    $field = $mapping->fresh()->fields->last();
    expect($field->options['rules']['extensions'])->toBe(['png', 'gif']);
});

test('the image field cannot have non image extensions', function () {
    $this->assertInvalidAddFieldRequest(
        FieldType::IMAGE(),
        ['rules' => ['extensions' => ['pdf']]],
        ['input.options.rules.extensions' => ['The selected extensions is invalid.']],
    );
});

test('an image can be made primary', function () {
    $user = createUser();
    $mapping = createMapping($user, [
        'fields' => [
            ['type' => 'SYSTEM_NAME', 'name' => 'Name'],
            ['type' => 'IMAGE', 'name' => 'Image 1', 'options' => ['primary' => true]],
        ],
    ]);

    $imageField = $mapping->fields->last();
    expect($imageField->option('primary'))->toBeTrue();
    $this->be($user)->sendAddFieldRequest(
        $mapping,
        FieldType::IMAGE(),
        ['primary' => true],
    );
    $mapping->refresh();
    $imageField = $mapping->fields->get(1);
    $newImage = $mapping->fields->last();
    expect($imageField->option('primary', false))->toBeFalse();
    expect($newImage->option('primary', false))->toBeTrue();
});

test('an image can be updated to primary', function () {
    $user = createUser();
    $mapping = createMapping($user, [
        'fields' => [
            ['type' => 'SYSTEM_NAME', 'name' => 'Name'],
            ['type' => 'IMAGE', 'name' => 'Image 1', 'options' => ['primary' => true]],
            ['type' => 'IMAGE', 'name' => 'Image 2', 'options' => ['primary' => false]],
        ],
    ]);

    $imageField = $mapping->fields->last();

    $this->be($user)->graphQL('
        mutation($input: UpdateMappingFieldInput!) {
            updateMappingField(input: $input) {
                code
                mapping { id }
            }
        }
        ',
        [
            'input' => [
                'id' => $imageField->id(),
                'mappingId' => $mapping->globalId(),
                'options' => ['primary' => true],
            ],
        ],
    );
    $mapping->refresh();
    $oldPrimaryImage = $mapping->fields->get(1);
    $imageField = $mapping->fields->last();
    expect($oldPrimaryImage->option('primary', false))->toBeFalse();
    expect($imageField->option('primary', false))->toBeTrue();
});

test('image field actions are formatted correctly', function () {
    $user = createUser();
    $firstImage = UploadedFile::fake()->image('field.jpg');
    $secondImage = UploadedFile::fake()->image('field2.jpg');

    $firstDocument = resolve(ImageRepository::class)->store($firstImage);
    $secondDocument = resolve(ImageRepository::class)->store($secondImage);

    $this->be($user)->assertItemUpdateCreatedActions(
        FieldType::IMAGE(),
        [],
        ['fieldValue' => ['image' => $firstDocument->id()]],
        ['fieldValue' => ['image' => $secondDocument->id()]],
        ['after' => 'field.jpg'],
        ['before' => 'field.jpg', 'after' => 'field2.jpg'],
    );
});

test('cropped image field actions are formatted correctly', function () {
    $user = createUser();
    $firstImage = UploadedFile::fake()->image('field.jpg');
    $secondImage = UploadedFile::fake()->image('field2.jpg');

    $firstDocument = resolve(ImageRepository::class)->store($firstImage);
    $secondDocument = resolve(ImageRepository::class)->store($secondImage);

    $this->be($user)->assertItemUpdateCreatedActions(
        FieldType::IMAGE(),
        ['croppable' => true],
        ['fieldValue' => ['image' => $firstDocument->id()]],
        ['fieldValue' => ['image' => $secondDocument->id()]],
        ['after' => 'field.jpg'],
        ['before' => 'field.jpg', 'after' => 'field2.jpg'],
    );
});

test('an image list field cannot be saved as primary', function () {
    $user = createUser();
    $mapping = createMapping($user);

    $this->be($user)->sendAddFieldRequest(
        $mapping,
        FieldType::IMAGE(),
        ['primary' => true, 'list' => true],
    )->assertSuccessfulGraphQL();

    $field = $mapping->fresh()->fields->last();
    expect($field->option('primary'))->toBeNull();
    expect($field->option('list'))->toBeTrue();

    $this->be($user)->graphQL('
        mutation($input: UpdateMappingFieldInput!) {
            updateMappingField(input: $input) {
                code
                mapping { id }
            }
        }
        ',
        [
            'input' => [
                'id' => $field->id(),
                'mappingId' => $mapping->globalId(),
                'options' => ['primary' => true, 'list' => true],
            ],
        ],
    );
    $field = $mapping->fresh()->fields->last();
    expect($field->option('primary'))->toBeNull();
});

test('items cannot be sorted by image fields', function () {
    $this->assertFieldIsNotSortable(FieldType::IMAGE());
});

test('changing an image shows the previous image name in actions', function () {
    $user = createUser();
    $firstImage = [
        'image' => UploadedFile::fake()->image('field.jpg'),
        'url' => '',
        'xOffset' => 0,
        'yOffset' => 0,
        'width' => 0,
        'height' => 0,
        'rotate' => 0,
    ];
    $secondImage = [
        'image' => UploadedFile::fake()->image('field2.jpg'),
        'url' => '',
        'xOffset' => 0,
        'yOffset' => 0,
        'width' => 0,
        'height' => 0,
        'rotate' => 0,
    ];

    enableAllActions();

    $mapping = $this->createMappingWithField($user, FieldType::IMAGE(), ['croppable' => false], 'field');

    $this->be($user)->sendCreateItemRequest($mapping, [
        'field' => ['fieldValue' => $firstImage],
    ], 'field { fieldValue { url } }');

    $item = $mapping->items()->first();
    $this->forgetLighthouseClasses();

    $this->be($user)->sendUpdateItemRequest($item, $mapping, [
        'field' => ['fieldValue' => $secondImage],
    ], 'field { fieldValue { url } }');

    $latestAction = $item->actions->first();

    $changes = $latestAction->changes();

    expect($changes[0]['after'])->toBe('field2.jpg')
        ->and($changes[0]['before'])->toBe('field.jpg');
});

test('updating an item with the same image does not create a new action', function () {
    $user = createUser();

    enableAllActions();

    $image = UploadedFile::fake()->image('field.jpg');

    $mapping = $this->createMappingWithField($user, FieldType::IMAGE(), ['croppable' => true], 'field');

    $this->be($user)->sendCreateItemRequest($mapping, [
        'field' => ['fieldValue' => [
            'image' => $image,
            'xOffset' => 2,
            'yOffset' => 2,
            'width' => 5,
            'height' => 5,
        ]],
    ], 'field { fieldValue { url } }');

    $item = $mapping->items()->first();

    $this->convertToFileRequest(route('graphql', $mapping->globalId()), [
        'query' => '
        mutation($id: ID!, $item: ItemItemDataInput) {
            items {
                items {
                    updateItem(input: { id: $id, data: $item }) { code }
                }
            }
        }
        ',
        'variables' => [
            'id' => $item->globalId(),
            'item' => [
                'name' => ['fieldValue' => 'New name'],
                'field' => ['fieldValue' => [
                    'image' => $image,
                    'xOffset' => 2,
                    'yOffset' => 2,
                    'width' => 5,
                    'height' => 5,
                ]],
            ],
        ],
    ]);

    $updateAction = $item->actions->first();
    expect($updateAction->changes())->toHaveCount(1)
        ->and($updateAction->changes()[0]['description'])->toBe('Changed the "Name"');
});
