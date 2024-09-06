<?php

declare(strict_types=1);

namespace AccountIntegrations\Core\Todos\Repositories;

use AccountIntegrations\Core\Todos\Todo;

interface MovableTodoRepository extends TodoRepository
{
    public function moveTodo(Todo $todo, ?string $previous = null, ?string $parent = null): Todo;
}
