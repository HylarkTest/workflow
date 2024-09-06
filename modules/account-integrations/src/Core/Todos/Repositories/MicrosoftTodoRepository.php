<?php

declare(strict_types=1);

namespace AccountIntegrations\Core\Todos\Repositories;

use Exception;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Microsoft\Graph\Model\ItemBody;
use Microsoft\Graph\Model\Importance;
use Microsoft\Graph\Model\TaskStatus;
use AccountIntegrations\Core\Todos\Todo;
use GuzzleHttp\Promise\PromiseInterface;
use Microsoft\Graph\Model\DateTimeTimeZone;
use AccountIntegrations\Core\Todos\TodoList;
use Microsoft\Graph\Model\RecurrencePattern;
use Microsoft\Graph\Model\WellknownListName;
use Microsoft\Graph\Model\PatternedRecurrence;
use Microsoft\Graph\Model\RecurrencePatternType;
use AccountIntegrations\Models\IntegrationAccount;
use AccountIntegrations\Core\MicrosoftGraphGateway;
use Microsoft\Graph\Model\TodoTask as MicrosoftTodo;
use Microsoft\Graph\Model\TodoTaskList as MicrosoftTodoList;

class MicrosoftTodoRepository implements TodoRepository
{
    protected MicrosoftGraphGateway $gateway;

    public function __construct(protected IntegrationAccount $account)
    {
        $this->gateway = resolve(MicrosoftGraphGateway::class, [$account]);
    }

    /**
     * @return \Illuminate\Support\Collection<int, \AccountIntegrations\Core\Todos\TodoList>
     */
    public function getTodoLists(): Collection
    {
        /*
         * The order that these are returned in is a bit confusing.
         * - First comes the lists that have been shared with the user
         *   in alphabetical order (ignoring case).
         * - Then comes the default "Tasks" list.
         * - After that comes all the lists owned by the user in alphabetical
         *   order (ignoring case).
         */
        $lists = $this->gateway->getCollection(
            '/me/todo/lists',
            MicrosoftTodoList::class,
            '',
            MicrosoftTodoList::class
        );

        return collect($lists)->map(function (MicrosoftTodoList $list) {
            return $this->buildListFromMicrosoftList($list);
        });
    }

    /**
     * @throws \Microsoft\Graph\Exception\GraphException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \JsonException
     */
    public function createTodoList(TodoList $list): TodoList
    {
        $response = $this->gateway->createItem(
            '/me/todo/lists',
            $this->buildMicrosoftListFromList($list),
            MicrosoftTodoList::class,
            (string) $list->id
        );

        return $this->buildListFromMicrosoftList($response);
    }

    /**
     * @throws \Microsoft\Graph\Exception\GraphException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \JsonException
     */
    public function updateTodoList(TodoList $list): TodoList
    {
        $listId = $this->getTodoListId($list->id);
        $response = $this->gateway->updateItem(
            "/me/todo/lists/$listId",
            $this->buildMicrosoftListFromList($list),
            MicrosoftTodoList::class,
            (string) $list->id
        );

        return $this->buildListFromMicrosoftList($response);
    }

    /**
     * @throws \Exception
     */
    public function deleteTodoList(string $listId): bool
    {
        $listId = $this->getTodoListId($listId);

        return $this->gateway->deleteItem(
            "/me/todo/lists/$listId",
            MicrosoftTodoList::class,
            $listId
        );
    }

    /**
     * @return \Illuminate\Support\Collection<int, \AccountIntegrations\Core\Todos\Todo>
     */
    public function getTodos(string $listId, array $options = []): Collection
    {
        $listId = $this->getTodoListId($listId);
        $tasks = $this->gateway->getCollection(
            $this->buildUrlFromOptions("/me/todo/lists/$listId/tasks", $options),
            MicrosoftTodoList::class,
            $listId,
            MicrosoftTodo::class
        );

        $list = $this->getTodoList($listId);

        return collect($tasks)->map(function (MicrosoftTodo $task) use ($list) {
            return $this->buildTodoFromMicrosoftTodo($task, $list);
        });
    }

    public function getTodo(string $listId, string $todoId): Todo
    {
        $listId = $this->getTodoListId($listId);
        $list = $this->getTodoList($listId);

        /** @var \Microsoft\Graph\Model\TodoTask $task */
        $task = $this->gateway->getItem(
            "/me/todo/lists/$listId/tasks/$todoId",
            MicrosoftTodo::class,
            $todoId,
            MicrosoftTodo::class
        );

        return $this->buildTodoFromMicrosoftTodo($task, $list);
    }

    /**
     * @throws \Microsoft\Graph\Exception\GraphException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \JsonException
     * @throws \Exception
     */
    public function createTodo(Todo $todo): Todo
    {
        $listId = $this->getTodoListId($todo->list->id);
        $todoTask = $this->buildMicrosoftTodoFromTodo($todo);
        $response = $this->gateway->createItem(
            '/me/todo/lists/'.$listId.'/tasks',
            $todoTask,
            MicrosoftTodoList::class,
            $listId
        );

        return $this->buildTodoFromMicrosoftTodo($response, $todo->list);
    }

    /**
     * @throws \Microsoft\Graph\Exception\GraphException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \JsonException
     * @throws \Exception
     */
    public function updateTodo(Todo $todo): Todo
    {
        $listId = $this->getTodoListId($todo->list->id);
        $todoTask = $this->buildMicrosoftTodoFromTodo($todo);
        $response = $this->gateway->updateItem(
            '/me/todo/lists/'.$listId.'/tasks/'.$todo->id,
            $todoTask,
            MicrosoftTodo::class,
            (string) $todo->id
        );

        return $this->buildTodoFromMicrosoftTodo($response, $todo->list);
    }

    /**
     * @throws \Exception
     */
    public function deleteTodo(string $listId, string $todoId): bool
    {
        $listId = $this->getTodoListId($listId);

        return $this->gateway->deleteItem(
            "/me/todo/lists/$listId/tasks/$todoId",
            MicrosoftTodo::class,
            $todoId
        );
    }

    public function getTodoList(string $listId): TodoList
    {
        $listId = $this->getTodoListId($listId);
        $todoList = $this->gateway->handleWaitPromise(
            $this->getTodoListPromise($listId),
            MicrosoftTodoList::class,
            $listId
        );

        return $this->buildListFromMicrosoftList($todoList);
    }

    protected function getTodoListPromise(string $listId): PromiseInterface
    {
        $listId = $this->getTodoListId($listId);

        return $this->gateway->getItemAsync("/me/todo/lists/$listId", MicrosoftTodoList::class);
    }

    /**
     * @throws \Microsoft\Graph\Exception\GraphException
     */
    protected function buildMicrosoftTodoFromTodo(Todo $todo): MicrosoftTodo
    {
        $todoTask = new MicrosoftTodo;
        if ($todo->id) {
            $todoTask->setId($todo->id);
        }
        if (isset($todo->name)) {
            $todoTask->setTitle($todo->name);
        }
        if (property_exists($todo, 'description')) {
            // TODO: Create stub for \Microsoft\Graph\Entity\TodoTask
            /** @phpstan-ignore-next-line Microsoft class is not typed properly, need to set null to unset */
            $todoTask->setBody($todo->description ? (new ItemBody)->setContent($todo->description) : null);
        }
        if (property_exists($todo, 'dueBy')) {
            /** @phpstan-ignore-next-line Microsoft class is not typed properly, need to set null to unset */
            $todoTask->setDueDateTime($todo->dueBy ? new DateTimeTimeZone([
                'dateTime' => $todo->dueBy->toISOString(),
                'timezone' => 'UTC',
            ]) : null);
        }
        if (property_exists($todo, 'completedAt')) {
            /** @phpstan-ignore-next-line Microsoft class is not typed properly, need to set null to unset */
            $todoTask->setCompletedDateTime($todo->completedAt ? new DateTimeTimeZone([
                'dateTime' => $todo->completedAt->toISOString(),
                'timezone' => 'UTC',
            ]) : null);
            $todoTask->setStatus(new TaskStatus($todo->completedAt ? TaskStatus::COMPLETED : TaskStatus::IN_PROGRESS));
        }
        if (property_exists($todo, 'priority')) {
            $todoTask->setImportance(new Importance(
                match ($todo->priority) {
                    1,2,3,4 => Importance::HIGH,
                    5,6,7,8 => Importance::NORMAL,
                    default => Importance::LOW,
                }
            ));
        }
        if (property_exists($todo, 'recurrence')) {
            /** @phpstan-ignore-next-line Microsoft class is not typed properly, need to set null to unset */
            $todoTask->setRecurrence($todo->recurrence ? $this->formatRecurrenceArray($todo->recurrence) : null);
            if ($todo->recurrence && ! ($todo->dueBy ?? null)) {
                $todoTask->setDueDateTime(new DateTimeTimeZone([
                    'dateTime' => today()->toISOString(),
                    'timezone' => 'UTC',
                ]));
            }
        }

        return $todoTask;
    }

    protected function buildMicrosoftListFromList(TodoList $list): MicrosoftTodoList
    {
        $microsoftList = new MicrosoftTodoList;
        $microsoftList->setDisplayName($list->name);
        if ($list->id) {
            $listId = $this->getTodoListId($list->id);
            $microsoftList->setId($listId);
        }

        return $microsoftList;
    }

    protected function buildListFromMicrosoftList(MicrosoftTodoList $microsoftList): TodoList
    {
        $listName = $microsoftList->getWellknownListName();

        return new TodoList(
            [
                'id' => $microsoftList->getId() ? $this->account->account_name.'::'.$microsoftList->getId() : '',
                'name' => $microsoftList->getDisplayName() ?: '',
                'isOwner' => $microsoftList->getIsOwner(),
                'isShared' => $microsoftList->getIsShared(),
                'isDefault' => $listName && ! $listName->is(WellknownListName::NONE),
            ],
            $this->account,
        );
    }

    /**
     * @throws \Exception
     */
    protected function buildTodoFromMicrosoftTodo(MicrosoftTodo $microsoftTodo, TodoList $list): Todo
    {
        $importance = $microsoftTodo->getImportance();
        $recurrence = $microsoftTodo->getRecurrence();
        $dueDateTime = $microsoftTodo->getDueDateTime();
        $completedDateTime = $microsoftTodo->getCompletedDateTime();

        return new Todo([
            'id' => $microsoftTodo->getId(),
            'name' => $microsoftTodo->getTitle(),
            'updatedAt' => Carbon::parse($microsoftTodo->getLastModifiedDateTime()),
            'description' => $microsoftTodo->getBody()?->getContent(),
            'dueBy' => $dueDateTime ? Carbon::parse($dueDateTime->getDateTime(), $dueDateTime->getTimeZone())->utc() : null,
            'completedAt' => $completedDateTime ? Carbon::parse($completedDateTime->getDateTime(), $completedDateTime->getTimeZone())->utc() : null,
            'priority' => match (true) {
                $importance?->is(Importance::HIGH) => 1,
                $importance?->is(Importance::NORMAL) => 5,
                $importance?->is(Importance::LOW) => 9,
                default => 0
            },
            'recurrence' => $this->buildRecurrenceArray($recurrence),
        ], $list, $this->account);
    }

    //
    //    public function moveTodoToList(Todo $todo, string $listId): bool
    //    {
    //        // TODO: Implement moveTodoToList() method.
    //    }
    //
    //    public function moveTodo(Todo $todo, string $previous = null, string $parent = null): Todo
    //    {
    //        // TODO: Implement moveTodo() method.
    //    }

    /**
     * @throws \Exception
     */
    protected function buildRecurrenceArray(?PatternedRecurrence $recurrence): ?array
    {
        $recurrencePattern = $recurrence?->getPattern();
        $type = $recurrencePattern?->getType();

        if (! $recurrencePattern || ! $type) {
            return null;
        }

        return [
            'frequency' => match (true) {
                $type->is(RecurrencePatternType::DAILY) => 'DAILY',
                $type->is(RecurrencePatternType::WEEKLY) => 'WEEKLY',
                $type->is(RecurrencePatternType::ABSOLUTE_MONTHLY) || $type->is(RecurrencePatternType::RELATIVE_MONTHLY) => 'MONTHLY',
                $type->is(RecurrencePatternType::ABSOLUTE_YEARLY) || $type->is(RecurrencePatternType::RELATIVE_YEARLY) => 'YEARLY',
                default => throw new Exception('Invalid recurrence type '.$type->value()),
            },
            'interval' => $recurrencePattern->getInterval(),
            // 'byDay' => $recurrencePattern->getDaysOfWeek(),
        ];
    }

    /**
     * @throws \Microsoft\Graph\Exception\GraphException
     * @throws \Exception
     */
    protected function formatRecurrenceArray(array $recurrence): PatternedRecurrence
    {
        $patternedRecurrence = new PatternedRecurrence;
        $type = new RecurrencePatternType(match ($recurrence['frequency']) {
            'DAILY' => RecurrencePatternType::DAILY,
            'WEEKLY' => RecurrencePatternType::WEEKLY,
            'MONTHLY' => RecurrencePatternType::ABSOLUTE_MONTHLY,
            'YEARLY' => RecurrencePatternType::ABSOLUTE_YEARLY,
            default => throw new Exception('Invalid recurrence type '.$recurrence['frequency']),
        });

        $pattern = new RecurrencePattern;
        $pattern->setType($type);
        $pattern->setInterval($recurrence['interval']);

        $patternedRecurrence->setPattern($pattern);

        return $patternedRecurrence;
    }

    protected function buildUrlFromOptions(string $baseUrl, array $options): string
    {
        $query = [];
        $filters = [];

        if (isset($options['search'])) {
            $query['$search'] = $options['search'];
        }

        if (isset($options['dueBefore'])) {
            $filters[] = 'dueDateTime/dateTime ne null';
            $filters[] = 'dueDateTime/dateTime lt '.Carbon::parse($options['dueBefore'])->toISOString();
        }

        if (isset($options['dueAfter'])) {
            $filters[] = 'dueDateTime/dateTime gt '.Carbon::parse($options['dueAfter'])->toISOString();
        }

        if (isset($options['isScheduled'])) {
            $filters[] = 'dueDateTime/dateTime ne null';
        }

        if (isset($options['maxPriority']) || isset($options['minPriority'])) {
            $importanceMap = [3 => 'high', 5 => 'normal', 7 => 'low'];

            $priorities = [];
            foreach ($importanceMap as $priority => $importance) {
                if ($priority <= ($options['maxPriority'] ?? 9) && $priority >= ($options['minPriority'] ?? 1)) {
                    $priorities[] = $importance;
                }
            }

            $filters[] = "importance in ('".implode("', '", $priorities)."')";
        }

        if (isset($options['filter'])) {
            match ($options['filter']) {
                'ONLY_COMPLETED' => $filters[] = "status eq 'completed'",
                'ONLY_INCOMPLETE' => $filters[] = "status ne 'completed'",
                default => null,
            };
        }

        if ($filters) {
            $query['$filter'] = implode(' and ', array_unique($filters));
        }

        if (isset($options['orderBy'])) {
            $orderMap = [
                'CREATED_AT' => 'createdDateTime',
                'DUE_BY' => 'dueDateTime/dateTime',
                'NAME' => 'title',
                'UPDATED_AT' => 'lastModifiedDateTime',
            ];
            $orders = [];

            foreach ($options['orderBy'] as $order) {
                $field = $orderMap[$order['field']] ?? null;
                if ($field) {
                    $orders[] = $field.' '.mb_strtolower($order['direction']);
                }
            }

            if ($orders) {
                $query['$orderBy'] = implode(',', $orders);
            }
        }

        $query['$top'] = $options['first'] ?? 25;

        $query['$skip'] = $query['$top'] * ($options['page'] ?? 0);

        $queryFields = [];

        foreach ($query as $field => $value) {
            $queryFields[] = "$field=$value";
        }

        return "$baseUrl?".implode('&', $queryFields);
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
}
