<?php

declare(strict_types=1);

namespace App\GraphQL\Queries\Features;

use App\Models\Base;
use App\Core\Features\Repositories\TodoItemRepository;

/**
 * @extends FeatureListItemQuery<\App\Models\Todo, \App\Models\TodoList>
 */
class TodoQuery extends FeatureListItemQuery
{
    public static string $itemQueryParams = <<<'GRAPHQL'
        dueBefore: DateTime
        dueAfter: DateTime
        isScheduled: Boolean
        isCompleted: Boolean
        priority: Int
        minPriority: Int
        maxPriority: Int
    GRAPHQL;

    protected function filterArgKeys(): array
    {
        return [
            ...parent::filterArgKeys(),
            'isCompleted',
            'dueBefore',
            'dueAfter',
            'isScheduled',
            'priority',
            'maxPriority',
            'minPriority',
        ];
    }

    protected function getCreateDataKeys(): array
    {
        return [
            'name',
            'startAt',
            'dueBy',
            'recurrence',
            'description',
            'location',
            'priority',
        ];
    }

    protected function getUpdateDataKeys(): array
    {
        return [
            ...$this->getCreateDataKeys(),
            'completedAt',
        ];
    }

    protected function repository(): TodoItemRepository
    {
        return resolve(TodoItemRepository::class);
    }

    protected function getListKey(): string
    {
        return 'todoList';
    }

    protected function getItemKey(): string
    {
        return 'todo';
    }

    protected function validateData(Base $base, array $data): void
    {
        if (! $base->accountLimits()->canCreateTodos()) {
            $this->throwValidationException('limit', trans('validation.exceeded'));
        }
    }
}
