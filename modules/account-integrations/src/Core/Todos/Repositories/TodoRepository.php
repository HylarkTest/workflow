<?php

declare(strict_types=1);

namespace AccountIntegrations\Core\Todos\Repositories;

use Illuminate\Support\Collection;
use AccountIntegrations\Core\Todos\Todo;
use AccountIntegrations\Core\Todos\TodoList;

interface TodoRepository
{
    /**
     * @return \Illuminate\Support\Collection<int, \AccountIntegrations\Core\Todos\TodoList>
     */
    public function getTodoLists(): Collection;

    public function getTodoList(string $listId): TodoList;

    public function createTodoList(TodoList $list): TodoList;

    public function updateTodoList(TodoList $list): TodoList;

    public function deleteTodoList(string $listId): bool;

    /**
     * @return \Illuminate\Support\Collection<int, \AccountIntegrations\Core\Todos\Todo>
     */
    public function getTodos(string $listId, array $options = []): Collection;

    public function getTodo(string $listId, string $todoId): Todo;

    public function createTodo(Todo $todo): Todo;

    public function updateTodo(Todo $todo): Todo;

    public function deleteTodo(string $listId, string $todoId): bool;
}
