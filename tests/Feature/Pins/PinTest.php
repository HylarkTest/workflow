<?php

declare(strict_types=1);

use Tests\Concerns\TestsFields;
use Illuminate\Http\UploadedFile;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(TestsFields::class);
uses(RefreshDatabase::class);

test('a user can change the image on a pin', function () {
    $user = createUser();

    $pinboard = createList($user, 'pinboard');
    $firstFile = [
        'image' => UploadedFile::fake()->image('image.jpg'),
        'url' => '',
        'xOffset' => 0,
        'yOffset' => 0,
        'width' => 0,
        'height' => 0,
        'rotate' => 0,
    ];

    $secondFile = [
        'image' => UploadedFile::fake()->image('image2.jpg', 100, 100),
        'url' => '',
        'xOffset' => 0,
        'yOffset' => 0,
        'width' => 0,
        'height' => 0,
        'rotate' => 0,
    ];

    $this->be($user)->assertGraphQLMutation(
        'createPin(input: $input)',
        ['input: CreatePinInput!' => [
            'name' => 'test',
            'pinboardId' => $pinboard->global_id,
            'image' => $firstFile,
        ]]
    );

    $pin = $pinboard->pins->first();
    $originalImage = $pin->image;

    $this->be($user)->assertGraphQLMutation(
        'updatePin(input: $input)',
        ['input: UpdatePinInput!' => [
            'id' => $pin->global_id,
            'name' => 'test',
            'description' => 'Woo',
            'image' => $secondFile,
        ]]
    );

    expect($originalImage->fresh())->trashed()->toBeTrue()
        ->and($pin->fresh()->image->filename)->toEqual('image2.jpg');
});

test('a pin is validated when image is not set', function () {
    $this->withGraphQLExceptionHandling();
    $user = createUser();

    $pinboard = createList($user, 'pinboard');

    $this->be($user)->assertFailedGraphQLMutation(
        'createPin(input: $input)',
        ['input: CreatePinInput!' => [
            'name' => 'test',
            'pinboardId' => $pinboard->global_id,
            'image' => null,
        ]]
    )->assertGraphQLValidationError('input.image', 'Upload an image to save this pin.');
}
);
