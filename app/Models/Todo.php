<?php

declare(strict_types=1);

namespace App\Models;

use GraphQL\Deferred;
use Illuminate\Support\Str;
use Actions\Core\ActionType;
use Planner\Models\Todo as BaseTodo;
use Illuminate\Database\Eloquent\Model;
use App\Models\Contracts\FeatureListItem;
use Sabre\VObject\Property\ICalendar\Recur;
use Actions\Models\Contracts\ModelActionRecorder;
use App\Models\Concerns\HasFeatureListItemMethods;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Todo
 *
 * @method \Illuminate\Database\Eloquent\Relations\BelongsTo<TodoList, Todo> todoList()
 *
 * @property \App\Models\TodoList $todoList
 *
 * @implements \App\Models\Contracts\FeatureListItem<\App\Models\TodoList, \App\Models\Todo>
 */
class Todo extends BaseTodo implements FeatureListItem, ModelActionRecorder
{
    /** @use \App\Models\Concerns\HasFeatureListItemMethods<\App\Models\TodoList> */
    use HasFeatureListItemMethods;

    /**
     * @var string[]
     */
    public array $actionIgnoredColumns = [
        'repeat_until',
        'uuid',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\TodoList, \App\Models\Todo>
     */
    public function list(): BelongsTo
    {
        return $this->todoList();
    }

    public function secondarySearchableArray(): array
    {
        return array_merge([
            [
                'text' => $this->description,
                'map' => 'description',
            ],
        ], $this->getAssigneesMappedForFinder());
    }

    public function getActionType(?Model $performer, ?ActionType $baseType): ActionType
    {
        if ($this->wasChanged('completed_at')) {
            return $this->isComplete() ?
                ActionType::COMPLETE() :
                ActionType::UNCOMPLETE();
        }

        return $baseType ?: ActionType::UPDATE();
    }

    public function getActionPayload(ActionType $type, ?Model $performer): ?array
    {
        if ($type->is(ActionType::COMPLETE()) || $type->is(ActionType::UNCOMPLETE())) {
            return null;
        }
        /** @var \Actions\Core\ActionRecorder $recorder */
        $recorder = static::getActionRecorder();

        return $recorder->getPayload($this, $type, $performer);
    }

    public static function formatOrderActionPayload(?int $order): ?string
    {
        return null;
    }

    public static function formatTodoListIdActionPayload(?int $todoListId): ?Deferred
    {
        return static::formatListIdActionPayload($todoListId);
    }

    public static function formatRecurrenceActionPayload(?string $recurrence): ?string
    {
        if (! $recurrence) {
            return null;
        }
        $recurrenceArray = Recur::stringToArray($recurrence);
        $frequency = $recurrenceArray['FREQ'];
        $interval = $recurrenceArray['INTERVAL'] ?? 1;
        $byDay = $recurrenceArray['BYDAY'] ?? null;

        $recurrenceString = trans_choice(
            'actions::description.todo.change.recurrence.every.'.Str::lower($frequency),
            $interval,
            ['interval' => $interval],
        );

        if (Str::lower($frequency) === 'weekly' && $byDay) {
            $days = \is_string($byDay) ? explode(',', $byDay) : $byDay;
            $days = implode(', ', array_map(static fn (string $day) => trans('common.dates.days.short.'.Str::lower($day)), $days));
            $recurrenceString .= ' '.trans('actions::description.todo.change.recurrence.on', ['days' => $days]);
        }

        return $recurrenceString;
    }

    public static function formatPriorityActionPayload(?int $priority): ?string
    {
        return match ($priority) {
            1, 2 => trans('actions::description.change.priority.urgent'),
            3, 4 => trans('actions::description.change.priority.high'),
            5, 6 => trans('actions::description.change.priority.normal'),
            default => trans('actions::description.change.priority.low'),
        };
    }
}
