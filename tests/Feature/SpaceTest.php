<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('a user can see their spaces', function () {
    $user = createUser();

    /** @var \App\Models\Space $defaultSpace */
    $defaultSpace = $user->firstSpace();
    $space = createSpace($user->firstPersonalBase());

    $this->be($user)->assertGraphQL([
        'spaces' => [
            'edges' => [
                ['node' => [
                    'id' => $defaultSpace->globalId(),
                    'name' => 'Personal',
                    'logo' => null,
                    'color' => null,
                    'createdAt' => $defaultSpace->created_at->toIso8601String(),
                    'updatedAt' => $defaultSpace->updated_at->toIso8601String(),
                ]],
                ['node' => [
                    'id' => $space->globalId(),
                    'name' => $space->name,
                    'logo' => $space->logo,
                    'color' => $space->color,
                    'createdAt' => $space->created_at->toIso8601String(),
                    'updatedAt' => $space->updated_at->toIso8601String(),
                ]],
            ],
        ],
    ]);
});

test('a user can create a space', function () {
    $user = createUser();

    $this->be($user)->assertGraphQLMutation(
        'createSpace(input: $input).code',
        ['input: CreateSpaceInput!' => [
            'name' => 'My new space',
        ]]
    );

    $base = $user->firstPersonalBase()->fresh();
    expect($base->spaces)->toHaveCount(2)
        ->and($base->spaces->last()->name)->toBe('My new space');
});

test('a user can update a space', function () {
    $user = createUser();

    /** @var \App\Models\Space $defaultSpace */
    $defaultSpace = $user->firstSpace();

    $this->be($user)->assertGraphQLMutation(
        'updateSpace(input: $input).code',
        ['input: UpdateSpaceInput!' => [
            'id' => $defaultSpace->global_id,
            'name' => 'My new space',
        ]]
    );

    $base = $user->firstPersonalBase()->fresh();
    expect($base->spaces)->toHaveCount(1)
        ->and($base->spaces->first()->name)->toBe('My new space');
});

test('a user can delete a space', function () {
    $user = createUser();
    $base = $user->firstPersonalBase();
    $space = createSpace($base);

    $this->be($user)->assertGraphQLMutation(
        'deleteSpace(input: $input).code',
        ['input: DeleteSpaceInput!' => [
            'id' => $space->global_id,
        ]]
    );

    $base->refresh();
    expect($base->spaces)->toHaveCount(1);
});

test('a user cannot delete the last space', function () {
    $this->withGraphQLExceptionHandling();
    $user = createUser();

    $space = $user->firstSpace();

    $this->be($user)->assertFailedGraphQLMutation(
        'deleteSpace(input: $input).code',
        ['input: DeleteSpaceInput!' => [
            'id' => $space->global_id,
        ]]
    )->assertGraphQLValidationError('input.id', 'The last space cannot be deleted.');

    $base = $user->firstPersonalBase()->fresh();
    expect($base->spaces)->toHaveCount(1);
});
