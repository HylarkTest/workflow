<?php

declare(strict_types=1);

use App\Models\TodoList;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('todo lists have incomplete count', function () {
    $user = createUser();
    $list = createTodoList($user, [], 2);
    /** @var \App\Models\Todo $todo */
    $todo = $list->todos()->first();
    $todo->complete();

    $this->be($user)
        ->assertGraphQL([
            "todoList(id: \"$list->global_id\")" => [
                'id' => $list->global_id,
                'todosCount' => 2,
                'incompleteTodosCount' => 1,
            ],
        ]);
});

test('todo actions are generated', function () {
    config(['actions.automatic' => true]);
    $user = createUser();
    $this->be($user);

    $todoList = createTodoList($user, ['name' => 'Inbox']);
    /** @var \App\Models\Todo $todo */
    $todo = $todoList->todos()->create(['name' => 'Do something']);

    $this->graphQL("
    {
        history(forNode: \"$todo->global_id\") {
            edges {
                node {
                    description
                    changes {
                        description
                        after
                        before
                        type
                    }
                }
            }
        }
    }
    ")->assertJsonCount(1, 'data.history.edges')
        ->assertJson(['data' => ['history' => ['edges' => [
            ['node' => ['changes' => [
                [
                    'description' => 'Added the name',
                    'before' => null,
                    'after' => 'Do something',
                ],
                [
                    'description' => 'Created on todo list',
                    'before' => null,
                    'after' => 'Inbox',
                ],
            ]]],
        ]]]]);
});

test('removing recurrence adds an appropriate action', function () {
    config(['actions.automatic' => true]);
    $user = createUser();
    $this->be($user);

    $todoList = createTodoList($user, ['name' => 'Inbox']);
    /** @var \App\Models\Todo $todo */
    $todo = $todoList->todos()->create(['name' => 'Do something', 'recurrence' => 'FREQ=DAILY;INTERVAL=1']);

    $this->graphQL("
    mutation RemoveRecurrence {
        updateTodo(input: { id: \"$todo->global_id}\", todoListId: \"$todoList->global_id\", recurrence: null }) {
            code
        }
    }
    ")->assertSuccessfulGraphQL();

    $todo = $todo->fresh();

    expect($todo->recurrence)->toBeNull();
    static::assertSame([[
        'description' => 'Removed the recurrence',
        'before' => 'every day',
        'after' => null,
        'type' => 'line',
    ]], $todo->latestAction->changes());
});

test('completing a todo creates a custom action', function () {
    config(['actions.automatic' => true]);
    $user = createUser();
    $this->be($user);

    $todoList = createTodoList($user, ['name' => 'Inbox']);
    /** @var \App\Models\Todo $todo */
    $todo = $todoList->todos()->create(['name' => 'Do something']);

    $this->graphQL("
    mutation UpdateTodo {
        updateTodo(input: { id: \"$todo->global_id}\", todoListId: \"$todoList->global_id\", completedAt: \"2022-08-09 00:00:00\" }) {
            code
        }
    }
    ")->assertSuccessfulGraphQL();

    $todo = $todo->fresh();

    expect($todo->isComplete())->toBeTrue()
        ->and($todo->latestAction->description(false))->toBe('Todo "Do something" marked as complete')
        ->and($todo->latestAction->description())->toBe('Todo "Do something" marked as complete by '.$user->name);
});

test('todos can be ordered by completed', function () {
    $user = createUser();
    /** @var \Planner\Models\TodoList $list */
    $list = createTodoList($user);
    $list->todos()->createMany([
        ['name' => 'First task'],
        ['name' => 'Second task'],
        ['name' => 'Third task', 'completed_at' => now()->subMinute()],
        ['name' => 'Fourth task'],
        ['name' => 'Fifth task', 'completed_at' => now()->subHour()],
    ]);

    $this->be($user)->graphQL('
    query SortedTodos($id: ID!) {
        manual: todos(todoListId: $id, orderBy: [{field: COMPLETED_AT, direction: DESC}, {field: MANUAL, direction: DESC}]) { edges { node { name } } }
        name: todos(todoListId: $id, orderBy: [{field: IS_COMPLETED, direction: ASC}, {field: NAME, direction: ASC}]) { edges { node { name } } }
    }
    ', ['id' => $list->global_id])->assertJson(['data' => [
        // Uncompleted tasks should be in the order they were created.
        // Completed tasks should be in the order they were completed.
        'manual' => ['edges' => [
            ['node' => ['name' => 'Fourth task']],
            ['node' => ['name' => 'Second task']],
            ['node' => ['name' => 'First task']],
            ['node' => ['name' => 'Third task']],
            ['node' => ['name' => 'Fifth task']],
        ]],
        'name' => ['edges' => [
            ['node' => ['name' => 'First task']],
            ['node' => ['name' => 'Fourth task']],
            ['node' => ['name' => 'Second task']],
            ['node' => ['name' => 'Fifth task']],
            ['node' => ['name' => 'Third task']],
        ]],
    ]], true);
});

test('todos can be filtered by completed', function () {
    $user = createUser();
    /** @var \Planner\Models\TodoList $list */
    $list = createTodoList($user);
    $list->todos()->createMany([
        ['name' => 'First task'],
        ['name' => 'Second task', 'completed_at' => now()->subMinute()],
    ]);

    $this->be($user)->graphQL('
    query filteredTodos($id: ID!) {
        all: todos(todoListId: $id) { edges { node { name } } }
        complete: todos(todoListId: $id, isCompleted: true) { edges { node { name } } }
        incomplete: todos(todoListId: $id, isCompleted: false) { edges { node { name } } }
    }
    ', ['id' => $list->global_id])->assertJson(['data' => [
        'all' => ['edges' => [
            ['node' => ['name' => 'Second task']],
            ['node' => ['name' => 'First task']],
        ]],
        'complete' => ['edges' => [
            ['node' => ['name' => 'Second task']],
        ]],
        'incomplete' => ['edges' => [
            ['node' => ['name' => 'First task']],
        ]],
    ]], true);
});

test('due date is required when recurrence is set', function () {
    $this->withGraphQLExceptionHandling();
    $user = createUser();
    /** @var \Planner\Models\TodoList $list */
    $list = createTodoList($user);

    $query = '
    mutation CreateTodo($input: CreateTodoInput!) {
        createTodo(input: $input) {
            code
            success
            todo {
                id
            }
            todoList {
                id
                incompleteTodosCount
            }
        }
    }
    ';

    $this->be($user)->graphQL($query, ['input' => [
        'todoListId' => $list->globalId,
        'name' => 'First todo',
        'recurrence' => ['frequency' => 'DAILY', 'interval' => 1],
    ]])->assertGraphQLValidationError('input.dueBy', 'The due by field is required when recurrence is present.');

    $this->withoutGraphQLExceptionHandling();
    $this->be($user)->graphQL($query, ['input' => [
        'todoListId' => $list->globalId,
        'name' => 'First todo',
        'dueBy' => now()->addDay()->toString(),
        'recurrence' => ['frequency' => 'DAILY', 'interval' => 1],
    ]])->assertSuccessfulGraphQL();
});

test('todos can be paginated when ordering by completed', function () {
    $user = createUser();
    /** @var \Planner\Models\TodoList $list */
    $list = createTodoList($user);
    $list->todos()->createMany([
        ['name' => 'First task'],
        ['name' => 'Second task'],
        ['name' => 'Third task', 'completed_at' => now()->subMinute()],
        ['name' => 'Fourth task'],
        ['name' => 'Fifth task', 'completed_at' => now()->subHour()],
    ]);

    $variables = [
        'id: ID' => $list->global_id,
        'order: [TodoOrderBy!]!' => [
            ['field' => 'COMPLETED_AT', 'direction' => 'DESC'],
            ['field' => 'MANUAL', 'direction' => 'DESC'],
        ],
    ];
    $cursor = $this->be($user)->assertGraphQL(
        [
            'todos(first: 3, todoListId: $id, orderBy: $order)' => [
                'edges' => [
                    ['node' => ['name' => 'Fourth task']],
                    ['node' => ['name' => 'Second task']],
                    ['node' => ['name' => 'First task']],
                ],
                'pageInfo' => ['endCursor' => new Ignore],
            ],
        ],
        $variables
    )->json('data.todos.pageInfo.endCursor');

    $this->be($user)->assertGraphQL(
        [
            'todos(after: $cursor, first: 3, todoListId: $id, orderBy: $order)' => [
                'edges' => [
                    ['node' => ['name' => 'Third task']],
                    ['node' => ['name' => 'Fifth task']],
                ],
            ],
        ],
        array_merge($variables, ['cursor: String' => $cursor])
    );
});

test('todos can be grouped by priority', function () {
    $user = createUser();

    $list = createTodoList($user, [], 10);
    $children = $list->children;
    $children[0]->update(['priority' => 0]);
    $children[1]->update(['priority' => 3]);
    $children[2]->update(['priority' => 1]);
    $children[3]->update(['priority' => 9]);
    $children[4]->update(['priority' => 5]);
    $children[5]->update(['priority' => 3]);
    $children[6]->update(['priority' => 0]);
    $children[7]->update(['priority' => 0]);
    $children[8]->update(['priority' => 3]);
    $children[9]->update(['priority' => 9]);

    $this->be($user)->assertGraphQL([
        'groupedTodos(group: "PRIORITY")' => [
            'groups' => [
                [
                    'groupHeader' => '0',
                    'edges' => [
                        ['node' => ['id' => $children[7]->global_id]],
                        ['node' => ['id' => $children[6]->global_id]],
                        ['node' => ['id' => $children[0]->global_id]],
                    ],
                    'pageInfo' => ['endCursor' => new Ignore],
                ],
                [
                    'groupHeader' => '1',
                    'edges' => [
                        ['node' => ['id' => $children[2]->global_id]],
                    ],
                    'pageInfo' => ['endCursor' => new Ignore],
                ],
                [
                    'groupHeader' => '3',
                    'edges' => [
                        ['node' => ['id' => $children[8]->global_id]],
                        ['node' => ['id' => $children[5]->global_id]],
                        ['node' => ['id' => $children[1]->global_id]],
                    ],
                    'pageInfo' => ['endCursor' => new Ignore],
                ],
                [
                    'groupHeader' => '5',
                    'edges' => [
                        ['node' => ['id' => $children[4]->global_id]],
                    ],
                    'pageInfo' => ['endCursor' => new Ignore],
                ],
                [
                    'groupHeader' => '9',
                    'edges' => [
                        ['node' => ['id' => $children[9]->global_id]],
                        ['node' => ['id' => $children[3]->global_id]],
                    ],
                    'pageInfo' => ['endCursor' => new Ignore],
                ],
            ],
        ],
    ]);
});

// Helpers
function createTodoList($user, $attributes = [], int $withChildren = 0): TodoList
{
    return createList($user, 'todoList', $attributes, $withChildren);
}
