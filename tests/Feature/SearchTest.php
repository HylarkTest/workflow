<?php

declare(strict_types=1);

use App\Models\Item;
use App\Models\Page;
use App\Models\Todo;
use App\Models\TodoList;
use Tests\Concerns\UsesElasticsearch;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);
uses(UsesElasticsearch::class);

uses()->group('es');

test('a user can search across their base', function () {
    $user = createUser();
    $space = $user->firstSpace();

    $mapping = createMapping($user, [
        'space_id' => $space,
        'fields' => [[
            'id' => 'NAME',
            'type' => 'SYSTEM_NAME',
            'name' => 'Name',
        ]],
    ]);
    $item = Item::query()->forceCreate([
        'mapping_id' => $mapping->id,
        'data' => ['NAME' => ['_v' => 'Thing']],
    ]);
    $todoList = TodoList::factory()->create(['space_id' => $space]);
    $todo = Todo::factory()->create(['todo_list_id' => $todoList, 'name' => 'Thing amabob']);
    $page = Page::factory()->create(['space_id' => $space, 'path' => 'Thing']);

    $response = $this->be($user)->graphQL('
    {
        search(query: "Thing") {
            edges {
                node {
                    __typename
                    id
                    ...on Page {
                        name
                    }
                    ...on Todo {
                        name
                    }
                    ...on Item {
                        id
                        name
                        names {
                            fieldId
                            value
                        }
                        images {
                            fieldId
                            value {
                                url
                            }
                        }
                        createdAt
                        pages {
                            id
                            name
                            symbol
                        }
                        mapping {
                            fields {
                                id
                                name
                                type
                            }
                        }
                    }
                }
                cursor
                highlights {
                    highlight
                    path
                }
            }
            pageInfo {
                hasNextPage
                hasPreviousPage
                startCursor
                endCursor
                total
                count
            }
        }
    }
    ')->assertJson([
        'data' => [
            'search' => [
                'edges' => [
                    [
                        'node' => [
                            'id' => $item->globalId(),
                            'name' => 'Thing',
                            'names' => [['fieldId' => 'NAME', 'value' => 'Thing']],
                            'images' => [],
                        ],
                    ],
                    [
                        'node' => [
                            '__typename' => 'EntitiesPage',
                            'id' => $page->globalId(),
                            'name' => 'Thing',
                        ],
                    ],
                    [
                        'node' => [
                            '__typename' => 'Todo',
                            'id' => $todo->globalId(),
                            'name' => 'Thing amabob',
                        ],
                    ],
                ],
            ],
        ],
    ]);

    $after = $response->json('data.search.edges.0.cursor');

    $this->be($user)->graphQL("
    {
        search(query: \"Thing\", after: \"$after\") {
            edges {
                node {
                    __typename
                    id
                }
                cursor
            }
            pageInfo {
                hasNextPage
                hasPreviousPage
                startCursor
                endCursor
                total
                count
            }
        }
    }
    ")->assertJson([
        'data' => [
            'search' => [
                'edges' => [
                    [
                        'node' => [
                            'id' => $page->globalId(),
                        ],
                    ],
                    [
                        'node' => [
                            'id' => $todo->globalId(),
                        ],
                    ],
                ],
            ],
        ],
    ]);
});

test('the finder searches with prefix', function () {
    $user = createUser();

    $list = TodoList::factory()->create(['space_id' => $user->firstSpace(), 'name' => 'Geraldine']);
    $todo = Todo::factory()->create(['todo_list_id' => $list, 'name' => 'Geraldine']);

    $this->be($user)->graphQL('
    {
        search(query: "geral") {
            edges {
                node {
                    __typename
                    id
                    ...on Todo {
                        name
                        todoList {
                            name
                        }
                    }
                }
                cursor
                highlights {
                    highlight
                    path
                }
            }
            pageInfo {
                hasNextPage
                hasPreviousPage
                startCursor
                endCursor
                total
                count
            }
        }
    }
    ')->assertJson([
        'data' => [
            'search' => [
                'edges' => [
                    [
                        'node' => [
                            '__typename' => 'Todo',
                            'id' => $todo->globalId(),
                            'name' => 'Geraldine',
                            'todoList' => [
                                'name' => 'Geraldine',
                            ],
                        ],
                        'highlights' => [
                            [
                                'highlight' => '<em>Geraldine</em>',
                                'path' => 'name',
                            ],
                            [
                                'highlight' => '<em>Geraldine</em>',
                                'path' => 'todoList.name',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ]);
});

test('Deleting a mapping removes the items from search', function () {
    $user = createUser();
    $space = $user->firstSpace();

    $mapping = createMapping($user, [
        'space_id' => $space,
        'fields' => [[
            'id' => 'NAME',
            'type' => 'SYSTEM_NAME',
            'name' => 'Name',
        ]],
    ]);
    Item::query()->forceCreate([
        'mapping_id' => $mapping->id,
        'data' => ['NAME' => ['_v' => 'Thing']],
    ]);

    $query = '
    {
        search(query: "Thing") {
            edges {
                node {
                    ...on Item {
                        id
                    }
                }
            }
        }
    }
    ';

    $this->be($user)->graphQL($query)->assertJsonCount(1, 'data.search.edges');

    $mapping->delete();

    $this->be($user)->graphQL($query)->assertJsonCount(0, 'data.search.edges');
});

test('the finder can search on assignees', function () {
    $user = createCollabUser();
    $user->name = 'Barbara';
    $user->save();

    /** @var \App\Models\Base $base */
    $base = $user->bases->last();
    $base->createDefaultAssigneeGroups();
    $base->initialize();
    $assigneeGroup = $base->defaultAssigneeGroup;

    /** @var \App\Models\TodoList $list */
    $list = createList($base, 'todoList', [], 1);
    /** @var \App\Models\Todo $todo */
    $todo = $list->children->first();

    $todo->assigneesForGroup($assigneeGroup)->attach($base->pivot, ['group_id' => $assigneeGroup->id]);
    $todo->globallySearchable();

    $this->be($user)->graphQL('
    {
        search(query: "Barbara") {
            edges {
                node {
                    __typename
                    id
                    ...on Todo {
                        assigneeGroups {
                            assignees {
                                id
                                name
                            }
                        }
                    }
                }
                cursor
                highlights {
                    highlight
                    path
                }
            }
            pageInfo {
                hasNextPage
                hasPreviousPage
                startCursor
                endCursor
                total
                count
            }
        }
    }
    ')->assertJson([
        'data' => [
            'search' => [
                'edges' => [
                    [
                        'node' => [
                            '__typename' => 'Todo',
                            'id' => $todo->globalId(),
                            'assigneeGroups' => [[
                                'assignees' => [[
                                    'id' => $base->pivot->globalId(),
                                    'name' => 'Barbara',
                                ]],
                            ]],
                        ],
                        'highlights' => [
                            [
                                'highlight' => '<em>Barbara</em>',
                                'path' => "assigneeGroups.{$assigneeGroup->global_id}.assignees.{$base->pivot->global_id}.name",
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ]);
});
