<?php

declare(strict_types=1);

use App\Models\Action;
use App\Core\Groups\Role;
use Actions\Core\ActionRecorder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('all actions can be fetched', function () {
    enableAllActions();
    $user = createUser();
    $model = createMapping($user);
    $action = $model->createAction;
    $pivot = tenant()->pivot;

    $this->be($user)->assertGraphQL([
        'history' => [
            'edges' => [
                ['node' => [
                    'id' => $action->globalId(),
                ]],
                ['node' => [
                    'id' => $user->firstSpace()->createAction->globalId(),
                ]],
                ['node' => [
                    'id' => $user->firstPersonalBase()->createAction->globalId(),
                ]],
            ],
        ],
    ]);
});

test('private actions can only be seen by the performer', function () {
    $admin = createCollabUser();
    $base = $admin->bases->last();
    $otherUser = createCollabUser(Role::MEMBER, $base);
    enableAllActions();
    tenancy()->initialize($base);
    $mapping = createMapping($base);
    $pivot = $otherUser->bases->find($base->id)->pivot;
    ActionRecorder::withPerformer($pivot, function () use ($pivot) {
        $pivot->forceFill(['name' => 'New name'])->save();
    });

    $this->be($admin)->assertGraphQL([
        'history' => [
            'edges' => [
                ['node' => [
                    'description' => "Blueprint \"$mapping->name\" created by $admin->name",
                ]],
            ],
        ],
    ]);

    $this->be($otherUser)->assertGraphQL([
        'history' => [
            'edges' => [
                ['node' => [
                    'description' => 'Personal base settings updated',
                ]],
                ['node' => [
                    'description' => "Blueprint \"$mapping->name\" created by $admin->name",
                ]],
            ],
        ],
    ]);
});

test('the history can be filtered for a single item', function () {
    enableAllActions();
    $user = createUser();
    $model = createMapping($user);
    createMapping($user);
    $action = $model->createAction;

    $this->be($user)->assertGraphQL(
        ["history(forNode: \"$model->global_id\")" => [
            'edges' => [['node' => [
                'id' => $action->globalId(),
            ]]],
        ]]
    );
});

test('the history can be filtered by the name of the subject', function () {
    enableAllActions();
    $user = createUser();
    $model = createMapping($user, ['name' => 'First mapping']);
    createMapping($user, ['name' => 'Second mapping']);
    $action = $model->createAction;

    $this->be($user)->assertGraphQL(
        ['history(search: "first")' => [
            'edges' => [['node' => [
                'id' => $action->globalId(),
            ]]],
        ]]
    );
});

test('actions are scoped by base', function () {
    enableAllActions();
    $otherUser = createUser();
    createMapping($otherUser, ['name' => 'Other mapping']);
    tenancy()->end();
    $user = createUser();
    createMapping($user, ['name' => 'Mapping']);
    $this->be($user);

    expect(Action::all())->toHaveCount(4)
        ->and(Action::all()->map->description(false))->toContain('Blueprint "Mapping" created')
        ->not->toContain('Page "Other mapping" created');
});

test('actions can be sorted', function () {
    enableAllActions();
    $user = createUser();
    $model = createMapping($user);
    $model->update(['name' => 'Blah']);
    $action = $model->createAction;
    $latestAction = $model->latestAction;

    $this->be($user)->assertGraphQL([
        "history(forNode: \"$model->global_id\", orderBy: [{field: CREATED_AT, direction: ASC}])" => [
            'edges' => [
                ['node' => [
                    'id' => $action->globalId(),
                ]],
                ['node' => [
                    'id' => $latestAction->globalId(),
                ]],
            ],
        ],
    ]);
});

test('actions can be filtered to only include the latest action', function () {
    enableAllActions();
    $user = createUser();
    $model = createMapping($user);

    // Perform multiple actions
    $model->update(['name' => 'First update']);
    $model->update(['name' => 'Second update']);
    $latestAction = $model->latestAction;

    $this->be($user)->assertGraphQL([
        "history(forNode: \"$model->global_id\", onlyLatestActions: true)" => [
            'edges' => [
                ['node' => [
                    'id' => $latestAction->globalId(),
                ]],
            ],
        ],
    ]);
});

test('actions can be filtered to only include actions where the subject has not been deleted', function () {
    enableAllActions();
    $user = createUser();
    $model = createMapping($user);

    // Perform actions
    $model->update(['name' => 'First update']);
    $model->update(['name' => 'Second update']);

    // Delete the mapping
    $model->delete();

    $this->be($user)->assertGraphQL([
        'history(subjectType: ["Mapping"], onlyExistingSubjects: true)' => [
            'edges' => new NullFieldWithSubQuery('{ node { id } }', true),
        ],
    ]);
});

test('actions cannot be filtered by undeleted subject without specifying the subject type(s)', function () {
    $this->withGraphQLExceptionHandling();
    enableAllActions();
    $user = createUser();
    $model = createMapping($user);

    // Perform actions
    $model->update(['name' => 'First update']);
    $model->update(['name' => 'Second update']);

    // Attempt to filter by undeleted subject without specifying subject type(s)
    $this->be($user)->assertGraphQL(
        'history(onlyExistingSubjects: true).edges.node.id', [], 'query', false
    )->assertGraphQLValidationError('onlyExistingSubjects', 'Either forNode or subjectType must be set when onlyExistingSubjects is true');
});
