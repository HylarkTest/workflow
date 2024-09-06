<?php

declare(strict_types=1);

use App\Models\Base;
use App\Core\BaseType;
use App\Core\Groups\Role;
use Illuminate\Http\UploadedFile;
use Tests\Mappings\Feature\Categories\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('bases can be fetched', function () {
    /** @var User $user */
    $user = createUser();

    $firstBase = $user->bases()->first();
    $base = Base::create(['name' => 'Group', 'type' => BaseType::COLLABORATIVE]);
    $user->bases()->attach($base, ['role' => Role::OWNER]);

    $this->be($user)->assertGraphQL([
        'bases' => [
            [
                'node' => [
                    'id' => $firstBase->global_id,
                    'name' => 'My base',
                    'members' => [['name' => $user->name]],
                ],
                'role' => 'OWNER',
            ],
            [
                'node' => [
                    'id' => $base->global_id,
                    'name' => 'Group',
                    'members' => [['name' => $user->name]],
                ],
                'role' => 'OWNER',
            ],
        ],
    ]);
});

test('a user can update the name of a collaborative base', function () {
    /** @var User $user */
    $user = createUser();

    $base = $user->bases()->create([
        'name' => 'Test base',
        'type' => BaseType::COLLABORATIVE,
    ], ['role' => Role::OWNER]);

    switchToBase($base);

    $this->be($user->fresh())->assertGraphQLMutation(
        'updateBase(input: $input)',
        ['input: UpdateBaseInput!' => [
            'name' => 'New name',
        ]],
    );

    expect($base->fresh())->name->toBe('New name');
});

test('a user cannot update their personal base', function () {
    $this->withGraphQLExceptionHandling();
    /** @var User $user */
    $user = createUser();

    $base = $user->bases()->first();

    $this->be($user)->assertFailedGraphQLMutation(
        'updateBase(input: $input)',
        ['input: UpdateBaseInput!' => [
            'name' => 'New name',
        ]],
    )->assertGraphQLValidationError('input.name', 'You cannot update the name of your personal base.');

    expect($base->fresh())->name->toBe('My base');
});

test('a user cannot update the name of a base they are a member of', function () {
    $this->withGraphQLExceptionHandling();
    /** @var User $user */
    $user = createUser();

    $base = $user->bases()->create([
        'name' => 'Test base',
        'type' => BaseType::COLLABORATIVE,
    ], ['role' => Role::MEMBER]);

    switchToBase($base, $user);

    $this->be($user->fresh())->assertFailedGraphQLMutation(
        'updateBase(input: $input)',
        ['input: UpdateBaseInput!' => [
            'name' => 'New name',
        ]],
    )->assertGraphQLUnauthorized();

    expect($base->fresh())->name->toBe('Test base');
});

test('a user can update the image of a collaborative base', function () {
    /** @var User $user */
    $user = createUser();

    $base = $user->bases()->create([
        'name' => 'Test base',
        'type' => BaseType::COLLABORATIVE,
    ], ['role' => Role::OWNER]);

    switchToBase($base, $user);

    $file = UploadedFile::fake()->image('logo.jpg');

    $this->be($user->fresh())->assertGraphQLMutation(
        'updateBase(input: $input)',
        ['input: UpdateBaseInput!' => [
            'image' => $file,
        ]],
    );

    expect($base->fresh())->image->toBe($file->hashName('base-images'));
});

test('a user can delete a base', function () {
    /** @var User $user */
    $user = createUser();

    $base = $user->bases()->create([
        'name' => 'Test base',
        'type' => BaseType::COLLABORATIVE,
    ], ['role' => Role::OWNER]);

    switchToBase($base);

    $this->be($user->fresh())->assertGraphQLMutation('deleteBase()');

    expect($user->bases()->count())->toBe(1);
});

test('a user can only delete a base they own', function () {
    $this->withGraphQLExceptionHandling();
    /** @var User $user */
    $user = createUser();

    $base = $user->bases()->create([
        'name' => 'Test base',
        'type' => BaseType::COLLABORATIVE,
    ], ['role' => Role::MEMBER]);

    switchToBase($base, $user);

    $this->be($user)->assertFailedGraphQLMutation('deleteBase()')
        ->assertGraphQLUnauthorized();

    expect($user->bases()->count())->toBe(2);
});

test('a user cannot delete their personal base', function () {
    $this->withGraphQLExceptionHandling();
    /** @var User $user */
    $user = createUser();

    $base = $user->bases()->first();

    $this->be($user)->assertFailedGraphQLMutation('deleteBase()')
        ->assertGraphQLUnauthorized();

    expect($user->bases()->count())->toBe(1);
});

test('a user can leave a base', function () {
    $owner = createUser();
    $user = createUser();

    $base = $owner->bases()->create([
        'name' => 'Test base',
        'type' => BaseType::COLLABORATIVE,
    ], ['role' => Role::OWNER]);

    $base->members()->attach($user, ['role' => Role::MEMBER]);

    expect($base->members()->count())->toBe(2);

    switchToBase($base, $user);

    $this->be($user->fresh())->assertGraphQLMutation('leaveBase()');

    expect($base->members()->count())->toBe(1);
});

test('a user cannot leave their personal base', function () {
    $this->withGraphQLExceptionHandling();
    $user = createUser();

    $this->be($user->fresh())->assertFailedGraphQLMutation('leaveBase()')
        ->assertGraphQLUnauthorized();
});

test('a user cannot leave a base if they are the only owner', function () {
    $this->withGraphQLExceptionHandling();
    $owner = createUser();

    $base = $owner->bases()->create([
        'name' => 'Test base',
        'type' => BaseType::COLLABORATIVE,
    ], ['role' => Role::OWNER]);

    switchToBase($base, $owner);

    $this->be($owner->fresh())->assertFailedGraphQLMutation('leaveBase()')
        ->assertGraphQLUnauthorized();
});
