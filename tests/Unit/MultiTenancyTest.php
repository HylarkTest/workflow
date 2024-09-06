<?php

declare(strict_types=1);

use App\Models\TodoList;
use App\Models\MarkerGroup;
use LighthouseHelpers\Utils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Eloquent\ModelNotFoundException;

uses(RefreshDatabase::class);

test('all queries to scoped models query the base id column', function () {
    $user = createUser();
    $todoList = TodoList::factory()->create(['space_id' => $user->firstSpace()]);

    $otherUser = createUser();
    $otherTodoList = TodoList::factory()->create(['space_id' => $otherUser->firstSpace()]);

    $base = $user->firstPersonalBase();
    tenancy()->initialize($base);

    expect(TodoList::query()->count())->toBe(1);
    expect($todoList->base_id)->toBe($base->id);
    expect($otherTodoList->base_id)->toBe($otherUser->firstPersonalBase()->id);
});

test('all queries with pivot tables query the base id column', function () {
    $user = createUser();
    $space = $user->firstSpace();
    $group = MarkerGroup::factory()->withMarkers()->create();
    $todoList = TodoList::factory()->withTodos()->create(['space_id' => $space]);
    $todoList->todos->first()->markers()->attach($group->markers->first());
    $todo = $todoList->todos->first();

    $otherUser = createUser();
    $otherSpace = $user->firstSpace();
    $otherGroup = MarkerGroup::factory()->withMarkers()->create();
    $otherTodoList = TodoList::factory()->withTodos()->create(['space_id' => $otherSpace]);
    $otherTodoList->todos->first()->markers()->attach($otherGroup->markers->first());
    $otherTodo = $otherTodoList->todos->first();

    expect($todo->markers()->getResults())->toBeEmpty();
    expect($otherTodo->markers()->getResults())->toHaveCount(1);

    tenancy()->initialize($user->firstPersonalBase());

    expect($otherTodo->markers()->getResults())->toBeEmpty();
    expect($todo->markers()->getResults())->toHaveCount(1);

    $tag = $todo->markers->first();
    $tag->forceFill(['base_id' => $otherUser->firstPersonalBase()->id])->save();
    $tag->pivot->update([
        'markable_id' => $otherTodo->id,
    ]);

    tenancy()->initialize($otherUser->firstPersonalBase());

    expect($todo->markers()->getResults())->toBeEmpty();
    expect($otherTodo->markers()->getResults())->toHaveCount(1);
});

test('models fetched by global id are scoped', function () {
    $user = createUser();
    $todoList = TodoList::factory()->create(['space_id' => $user->firstSpace()]);

    $otherUser = createUser();
    $otherTodoList = TodoList::factory()->create(['space_id' => $otherUser->firstSpace()]);

    tenancy()->initialize($user->firstPersonalBase());

    expect(Utils::resolveModelFromGlobalId($todoList->global_id)->is($todoList))->toBeTrue();
    $exceptionThrown = false;
    try {
        Utils::resolveModelFromGlobalId($otherTodoList->global_id);
    } catch (ModelNotFoundException $error) {
        $exceptionThrown = true;
    }
    expect($exceptionThrown)->toBeTrue();
});
