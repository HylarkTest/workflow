<?php

declare(strict_types=1);

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('a user can see their details', function () {
    $user = createUser([
        'name' => 'Anakin Skywalker',
        'email' => 'as@jedi.com',
        'avatar' => 'avatars/abc.jpg',
    ]);

    $this->be($user)->assertGraphQL([
        'me' => [
            'id' => $user->globalId(),
            'name' => 'Anakin Skywalker',
            'email' => 'as@jedi.com',
            'avatar' => '/avatars/abc.jpg',
            'createdAt' => $user->created_at->toIso8601String(),
            'updatedAt' => $user->updated_at->toIso8601String(),
        ],
    ]);
});

test('a user can change their name and email', function () {
    $user = createUser([
        'name' => 'Anakin Skywalker',
        'email' => 'as@jedi.com',
        'avatar' => null,
    ]);

    $user->firstPersonalBase()->run(function () use ($user) {
        $originalAvatar = UploadedFile::fake()->image('anakin.jpg');

        $user->updateImage($originalAvatar, 'avatar', 'avatars');
        expect(Storage::disk('images')->exists($user->avatar))->toBeTrue();
    });

    $file = UploadedFile::fake()->image('vader.jpg');

    $originalAvatar = $user->avatar;

    $this->be($user)->assertGraphQLMutation(
        ['updateMe(input: $input)' => [
            'user' => [
                'id' => $user->globalId(),
                'name' => 'Darth Vader',
                'email' => 'dv@sith.com',
                'avatar' => $file->hashName('/avatars'),
            ],
        ]],
        ['input: UpdateUserInput!' => [
            'name' => 'Darth Vader',
            'email' => 'dv@sith.com',
            'avatar' => $file,
        ]]
    );

    $user->firstPersonalBase()->run(fn () => expect(Storage::disk('images')->exists($originalAvatar))->toBeFalse());
});

test('a user cannot change to an empty name', function () {
    $this->withGraphQLExceptionHandling();
    $user = createUser([
        'name' => 'Anakin Skywalker',
        'email' => 'as@jedi.com',
        'avatar' => null,
    ]);

    $this->be($user)->assertFailedGraphQLMutation(
        'updateMe(input: $input).code',
        ['input: UpdateUserInput!' => ['name' => '']],
    )->assertGraphQLValidationError('input.name', 'The full name field must have a value.');
});
