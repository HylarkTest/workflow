<?php

declare(strict_types=1);

namespace AccountIntegrations\Core\Todos\Repositories;

use Google\Service\Tasks;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use AccountIntegrations\Core\Todos\Todo;
use AccountIntegrations\Core\Todos\TodoList;
use Google\Service\Tasks\Task as GoogleTodo;
use AccountIntegrations\Core\GoogleRepository;
use AccountIntegrations\Models\IntegrationAccount;
use Google\Service\Tasks\TaskList as GoogleTodoList;
use AccountIntegrations\Exceptions\ResourceNotFoundException;

class GoogleTodoRepository extends GoogleRepository implements MovableTodoRepository
{
    protected Tasks $taskApi;

    public function __construct(IntegrationAccount $account)
    {
        parent::__construct($account);
        $this->taskApi = new Tasks($this->client);
    }

    /**
     * @return \Illuminate\Support\Collection<int, \AccountIntegrations\Core\Todos\TodoList>
     */
    public function getTodoLists(): Collection
    {
        /** @var array<int, \Google\Service\Tasks\TaskList> $lists */
        $lists = $this->makeRequest(
            fn () => $this->taskApi->tasklists->listTasklists()
        );

        return collect($lists)->map(function (GoogleTodoList $list, int $index) {
            return $this->buildListFromGoogleList($list, $index);
        });
    }

    public function getTodoList(string $listId): TodoList
    {
        $listId = $this->getTodoListId($listId);
        $list = $this->makeRequest(
            fn () => $this->taskApi->tasklists->get($listId),
            GoogleTodoList::class,
            $listId
        );

        return $this->buildListFromGoogleList($list);
    }

    public function createTodoList(TodoList $list): TodoList
    {
        $body = $this->buildGoogleListFromList($list);
        $googleList = $this->makeRequest(
            fn () => $this->taskApi->tasklists->insert($body)
        );

        return $this->buildListFromGoogleList($googleList);
    }

    public function updateTodoList(TodoList $list): TodoList
    {
        $body = $this->buildGoogleListFromList($list);
        $listId = $body->getId();
        $this->getTodoListRequest($listId);
        $googleList = $this->makeRequest(
            fn () => $this->taskApi->tasklists->update($listId, $body)
        );

        return $this->buildListFromGoogleList($googleList);
    }

    public function deleteTodoList(string $listId): bool
    {
        $listId = $this->getTodoListId($listId);
        $this->getTodoListRequest($listId);
        $this->makeRequest(
            fn () => $this->taskApi->tasklists->delete($listId),
            GoogleTodoList::class,
            $listId
        );

        return true;
    }

    /**
     * @return \Illuminate\Support\Collection<int, \AccountIntegrations\Core\Todos\Todo>
     */
    public function getTodos(string $listId, array $options = []): Collection
    {
        $listId = $this->getTodoListId($listId);
        /** @var array<int, \Google\Service\Tasks\Task> $todos */
        $todos = $this->makeRequest(
            fn () => $this->taskApi->tasks->listTasks($listId, $this->buildParamsFromOptions($options)),
            GoogleTodoList::class,
            $listId
        );
        $list = $this->getTodoList($listId);

        return collect($todos)->map(function (GoogleTodo $task) use ($list) {
            return $this->buildTodoFromGoogleTodo($task, $list);
        });
    }

    public function getTodo(string $listId, string $todoId): Todo
    {
        $listId = $this->getTodoListId($listId);
        $list = $this->getTodoList($listId);
        $todo = $this->makeRequest(
            fn () => $this->taskApi->tasks->get($listId, $todoId),
            GoogleTodo::class,
            $todoId
        );
        $this->checkTodoDeleted($todo, $todoId);

        return $this->buildTodoFromGoogleTodo($todo, $list);
    }

    public function createTodo(Todo $todo): Todo
    {
        $listId = $this->getTodoListId($todo->list->id);
        $body = $this->buildGoogleTodoFromTodo($todo);
        $googleTodo = $this->makeRequest(
            fn () => $this->taskApi->tasks->insert($listId, $body),
            GoogleTodoList::class,
            $listId
        );

        return $this->buildTodoFromGoogleTodo($googleTodo, $todo->list);
    }

    public function updateTodo(Todo $todo): Todo
    {
        $listId = $this->getTodoListId($todo->list->id);
        $body = $this->buildGoogleTodoFromTodo($todo);
        $googleTodo = $this->makeRequest(
            fn () => $this->taskApi->tasks->patch($listId, $todo->id, $body),
            GoogleTodo::class,
            $todo->id
        );

        $this->checkTodoDeleted($googleTodo, (string) $todo->id);

        return $this->buildTodoFromGoogleTodo($googleTodo, $todo->list);
    }

    public function deleteTodo(string $listId, string $todoId): bool
    {
        $listId = $this->getTodoListId($listId);
        $todo = $this->makeRequest(
            fn () => $this->taskApi->tasks->get($listId, $todoId),
            GoogleTodo::class,
            $todoId
        );
        $this->checkTodoDeleted($todo, $todoId);
        $this->makeRequest(
            fn () => $this->taskApi->tasks->delete($listId, $todoId),
            GoogleTodo::class,
            $todoId
        );

        return true;
    }

    public function moveTodo(Todo $todo, ?string $previous = null, ?string $parent = null): Todo
    {
        $listId = $this->getTodoListId($todo->list->id);
        $googleTodo = $this->makeRequest(
            fn () => $this->taskApi->tasks->move($listId, $todo->id, [
                'previous' => $previous,
                'parent' => $parent,
            ])
        );

        return $this->buildTodoFromGoogleTodo($googleTodo, $todo->list);
    }

    protected function buildGoogleTodoFromTodo(Todo $todo): GoogleTodo
    {
        $todoTask = new GoogleTodo;
        if ($todo->id) {
            $todoTask->setId($todo->id);
        }
        $todoTask->setTitle($todo->name);
        if (property_exists($todo, 'description')) {
            $todoTask->setNotes($todo->description);
        }
        if (property_exists($todo, 'dueBy')) {
            $todoTask->setDue($todo->dueBy?->toRfc3339String());
        }

        if (property_exists($todo, 'completedAt')) {
            $todoTask->setCompleted($todo->completedAt?->toRfc3339String());
            $todoTask->setStatus($todo->completedAt ? 'completed' : 'needsAction');
        }

        return $todoTask;
    }

    protected function buildGoogleListFromList(TodoList $list): GoogleTodoList
    {
        $googleList = new GoogleTodoList;
        $googleList->setTitle($list->name);
        if ($list->id) {
            $googleList->setId($this->getTodoListId($list->id));
        }

        return $googleList;
    }

    protected function buildListFromGoogleList(GoogleTodoList $googleList, ?int $index = null): TodoList
    {
        return new TodoList(
            [
                'id' => $googleList->getId() ? $this->account->account_name.'::'.$googleList->getId() : '',
                'name' => $googleList->getTitle() ?: '',
                'isOwner' => true,
                'isShared' => false,
                'isDefault' => $index === 0,
                'updatedAt' => Carbon::parse($googleList->getUpdated()),
            ],
            $this->account,
        );
    }

    protected function buildTodoFromGoogleTodo(GoogleTodo $googleTodo, TodoList $list): Todo
    {
        $dueDateTime = $googleTodo->getDue();
        $completedDateTime = $googleTodo->getCompleted();

        return new Todo([
            'id' => $googleTodo->getId(),
            'name' => $googleTodo->getTitle(),
            'updatedAt' => Carbon::parse($googleTodo->getUpdated()),
            'description' => $googleTodo->getNotes(),
            'dueBy' => $dueDateTime ? Carbon::parse($dueDateTime)->utc() : null,
            'completedAt' => $completedDateTime ? Carbon::parse($completedDateTime) : null,
            'priority' => 0,
        ], $list, $this->account);
    }

    protected function buildParamsFromOptions(array $options): array
    {
        $query = [];

        $startOfTime = Carbon::parse('1900-01-01')->toRfc3339String();

        if (isset($options['dueBefore'])) {
            $query['dueMax'] = Carbon::parse($options['dueBefore'])->toRfc3339String();
        }

        if (isset($options['dueAfter'])) {
            $query['dueMin'] = Carbon::parse($options['dueAfter'])->toRfc3339String();
        }

        if (isset($options['isScheduled']) && ! isset($query['dueMin'])) {
            // A bit of a hack, just assuming that no tasks would be scheduled
            // more than a century ago so this should return all scheduled tasks.
            $query['dueMin'] = $startOfTime;
        }

        if (isset($options['filter'])) {
            match ($options['filter']) {
                'ONLY_COMPLETED' => $query['completedMin'] = $startOfTime,
                'ONLY_INCOMPLETE' => $query['showCompleted'] = false,
                default => null,
            };
        }

        $query['maxResults'] = $options['first'] ?? 25;

        if (isset($options['page'])) {
            $query['pageToken'] = $options['page'];
        }

        return $query;
    }

    protected function getTodoListId(?string $todoListId): string
    {
        if (! $todoListId) {
            return '';
        }

        return Str::startsWith($todoListId, $this->account->account_name.'::')
            ? mb_substr($todoListId, mb_strlen($this->account->account_name) + 2)
            : $todoListId;
    }

    /**
     * @throws \AccountIntegrations\Exceptions\InvalidGrantException
     */
    public function getTodoListRequest(string $listId): void
    {
        $this->makeRequest(
            fn () => $this->taskApi->tasklists->get($listId),
            GoogleTodoList::class,
            $listId
        );
    }

    public function checkTodoDeleted(GoogleTodo $todo, string $todoId): void
    {
        if ($todo->getDeleted()) {
            $exception = new ResourceNotFoundException;
            $exception->setIntegration($this->account, GoogleTodo::class, $todoId);
            throw $exception;
        }
    }
}
