<?php

declare(strict_types=1);

namespace Planner\Models;

use Illuminate\Support\Str;
use Planner\Core\TodoStatus;
use Illuminate\Support\Carbon;
use Planner\Events\TodoCompleted;
use Sabre\VObject\Component\VCalendar;
use Sabre\VObject\Recur\RRuleIterator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Planner\Database\Factories\TodoFactory;
use Sabre\VObject\Property\ICalendar\Recur;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use LaravelUtils\Database\Eloquent\Contracts\Sortable;
use LaravelUtils\Database\Eloquent\Concerns\IsSortable;

/**
 * Attributes
 *
 * @property int $id
 * @property string $uuid
 * @property int $todo_list_id
 * @property int|null $parent_id
 * @property string $name
 * @property string|null $description
 * @property \Planner\Core\TodoStatus $status
 * @property \Illuminate\Support\Carbon|null $completed_at
 * @property \Illuminate\Support\Carbon|null $due_by
 * @property array|null $recurrence
 * @property \Illuminate\Support\Carbon|null $repeat_until
 * @property int $priority
 * @property string|null $location
 * @property \Illuminate\Support\Carbon|null $remind_at
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 *
 * Accessors
 * @property string $recurrenceRule
 *
 * Relationships
 * @property \Planner\Models\Todo|null $parent
 * @property \Planner\Models\TodoList $todoList
 *
 * @implements \LaravelUtils\Database\Eloquent\Contracts\Sortable<\Planner\Models\Todo>
 */
class Todo extends Model implements Sortable
{
    use HasFactory;
    use IsSortable;

    public const ICAL_DATETIME_FORMAT = 'Ymd\\THis\\Z';

    public const ICAL_DATE_FORMAT = 'Ymd';

    public const WEEKDAY_MAP = [
        'MO', 'TU', 'WE', 'TH', 'FR', 'SA', 'SU',
    ];

    /**
     * @return array<string, string>
     */
    public function getCasts(): array
    {
        $casts = parent::getCasts();

        return array_merge($casts, [
            'status' => TodoStatus::class,
            'completed_at' => 'datetime',
            'due_by' => 'datetime',
            'repeat_until' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ]);
    }

    /**
     * Get the fillable attributes for the model.
     */
    public function getFillable(): array
    {
        $fillable = parent::getFillable();

        return array_merge($fillable, [
            'name',
            'priority',
            'completed_at',
            'due_by',
            'recurrence',
            'repeat_until',
            'description',
            'remind_at',
            'status',
        ]);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Casts\Attribute<int, int>
     */
    public function priority(): Attribute
    {
        return Attribute::get(fn (?int $priority): int => $priority ?? 0);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Casts\Attribute<string|null, string|null>
     */
    public function recurrenceRule(): Attribute
    {
        return Attribute::get(fn ($_, $attributes = []): ?string => $attributes['recurrence'] ?? null);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Casts\Attribute<array<string, mixed>|null, string|array<string, mixed>|null>
     */
    public function recurrence(): Attribute
    {
        return new Attribute(
            get: function (?string $recurrenceRule): ?array {
                if ($recurrenceRule) {
                    $recurrence = Recur::stringToArray($recurrenceRule);

                    $byDay = $recurrence['BYDAY'] ?? [];
                    if (\is_string($byDay)) {
                        $byDay = explode(',', $byDay);
                    }

                    return [
                        'frequency' => $recurrence['FREQ'],
                        'interval' => $recurrence['INTERVAL'] ?? 1,
                        'byDay' => $byDay,
                        'count' => $recurrence['COUNT'] ?? null,
                    ];
                }

                return null;
            },
            set: function (string|array|null $recurrence): ?string {
                if (\is_array($recurrence)) {
                    $recurrence = collect($recurrence)
                        ->filter()
                        ->map(function ($value, string $key) {
                            if (mb_strtolower($key) === 'frequency') {
                                $key = 'FREQ';
                            }
                            $key = mb_strtoupper($key);

                            if ($key === 'BYDAY') {
                                $value = (array) $value;
                            }

                            return "$key=".(\is_array($value) ? implode(',', $value) : $value);
                        })
                        ->join(';');
                }

                return $recurrence ?: null;
            }
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Planner\Models\TodoList, \Planner\Models\Todo>
     */
    public function todoList(): BelongsTo
    {
        return $this->belongsTo(config('planner.models.todo_list'), 'todo_list_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Planner\Models\Todo, \Planner\Models\Todo>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(config('planner.models.todo'), 'parent_id');
    }

    /**
     * ICS is the iCalendar specification that allows integration with other
     * platforms. The available fields are as follows.
     *
     * UID -> A unique identifier for the calendar
     * DTSTAMP -> The time the iCalendar instance was created
     * CLASS -> Defines the access (PUBLIC/PRIVATE/CONFIDENTIAL)
     * COMPLETED -> The time the task was completed
     * CREATED -> The time the task was created (could be different to the DTSTAMP, not for us though)
     * DESCRIPTION -> A detailed description of what the task represents
     * DTSTART -> The time that the task can be started
     * GEO -> The latitude and longitude associated with the task
     * LAST-MODIFIED -> The date the task was last modified
     * LOCATION -> A string representation of the venue for the activity
     * ORGANIZER -> An identifier of the person that organized the task e.g. CN=John Smith:MAILTO:jsmith@host1.com
     * PERCENT -> An integer showing how complete the activity is
     * PRIORITY -> An integer between 0 and 9 showing the importance of the task 1 is the highest, 9 is the lowest, 0 indicates undefined priority
     * RECURRENCE-ID -> Identifies a specific recurring task
     * SEQUENCE -> Defines the revision of the task, starts at 0 and increments after significant changes
     * STATUS -> One of NEEDS-ACTION/COMPLETED/IN-PROGRESS/CANCELLED
     * SUMMARY -> A short description of the task
     * URL -> A url that defines a more dynamic rendition of the calendar information
     * RRULE -> Defines the repeating behaviour of the task, e.g. FREQ=DAILY;COUNT=10/FREQ=DAILY;INTERVAL=2;FROM=COMPLETION/FREQ=WEEKLY;INTERVAL=1;BYDAY=MO;WE;FR;SA
     * DUE -> The due date of the task
     * DURATION -> A string indicating how long the activity will take e.g. 15 days, 5 hours, and 20 seconds looks like P15DT5H0M20S
     * ATTACH -> A url to a document associated with the task (can be more than one)
     * ATTENDEE -> An identifier of a person attending the task (can be more than one)
     * CATEGORIES -> A comma separated list of tags
     * COMMENT -> Notes about the task (can be more than one)
     * CONTACT -> An identifier of a person somehow related to the task (can be more than one)
     * EXDATE -> A comma separated list of times that should be excluded from a recurring rule
     * RELATED-TO -> Links to the parent task
     * RESOURCES -> A comma separated list of things necessary for the completion of the task
     * RDATE -> A list of times for a recurrence set
     *
     * More information about these fields and the correct format can be found
     * at https://www.kanzaki.com/docs/ical/
     */
    public function toIcs(): VCalendar
    {
        $props = [
            'PRODID' => config('app.name'),
            'DTSTAMP' => $this->created_at->format(self::ICAL_DATETIME_FORMAT),
            'UID' => $this->uuid,
            'CREATED' => $this->created_at->format(self::ICAL_DATETIME_FORMAT),
            'LAST-MODIFIED' => $this->updated_at->format(self::ICAL_DATETIME_FORMAT),
            'SUMMARY' => $this->name,
            'PRIORITY' => $this->priority,
            'STATUS' => $this->status->value,
        ];
        if ($this->completed_at) {
            $props['COMPLETED'] = $this->completed_at->format(self::ICAL_DATETIME_FORMAT);
        }
        if ($this->description) {
            $props['DESCRIPTION'] = $this->description;
        }
        if ($this->location) {
            $props['LOCATION'] = $this->location;
        }
        if ($this->recurrence) {
            $props['RRULE'] = $this->recurrence;
        }
        if ($this->due_by) {
            $props['DUE'] = $this->due_by->format(
                $this->due_by->rawFormat('H:i') === '23:59' ?
                    self::ICAL_DATE_FORMAT :
                    self::ICAL_DATETIME_FORMAT
            );
        }
        if ($this->parent) {
            $props['RELATED-TO'] = $this->parent->uuid;
        }

        return new VCalendar(['VTODO' => $props]);
    }

    public function complete(): void
    {
        $this->completed_at = now();
        $this->save();
    }

    public function uncomplete(): void
    {
        $this->completed_at = null;
        $this->save();
    }

    public function moveToNextRecurrence(): void
    {
        $iterator = $this->getRecurrenceIterator();

        if (! $iterator) {
            return;
        }

        $iterator->next();

        if ($newDate = $iterator->current()) {
            $this->due_by = $newDate;
            $this->decrementCountIfSet();
            $this->uncomplete();
        }
    }

    public function isComplete(): bool
    {
        return $this->completed_at !== null;
    }

    public function isInfinite(): bool
    {
        return (bool) $this->getRecurrenceIterator()?->isInfinite();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder<\Planner\Models\Todo>
     */
    public function buildSortQuery(): Builder
    {
        return static::query()->where('todo_list_id', $this->todo_list_id);
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<\Planner\Models\Todo>  $query
     * @return \Illuminate\Database\Eloquent\Builder<\Planner\Models\Todo>
     */
    public function scopeIncomplete(Builder $query): Builder
    {
        return $query->whereNull('completed_at');
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<\Planner\Models\Todo>  $query
     * @return \Illuminate\Database\Eloquent\Builder<\Planner\Models\Todo>
     */
    public function scopeCompleted(Builder $query): Builder
    {
        return $query->whereNotNull('completed_at');
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<\Planner\Models\Todo>  $query
     */
    public function scopeInRange(Builder $query, Carbon $from, Carbon $to): void
    {
        // If we are fetching the recurring instances then we need to filter
        // all events that start before the end of the range and end after
        // the beginning of the range, as well as fetching all recurring
        // events that start before the start of the range and end after the
        // start of the range.
        $query->where(function (Builder $query) use ($from, $to) {
            $query->where(function (Builder $query) use ($from, $to) {
                $query->dueBefore($to)
                    ->dueAfter($from);
            });
        });
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<\Planner\Models\Todo>  $query
     */
    public function scopeDueBefore(Builder $query, Carbon $date): void
    {
        $query->where(function (Builder $query) use ($date) {
            $query->where('due_by', '<', $date)
                ->orWhere('due_by', $date->endOfDay());
        });
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<\Planner\Models\Todo>  $query
     */
    public function scopeDueAfter(Builder $query, Carbon $date): void
    {
        $query->where(function (Builder $query) use ($date) {
            $query->where('due_by', '>', $date)
                ->orWhere('due_by', $date->endOfDay());
        });
    }

    protected function decrementCountIfSet(): void
    {
        $recurrence = $this->recurrence;
        if (! $recurrence) {
            return;
        }
        if (isset($recurrence['count'])) {
            $recurrence['count']--;

            if ($recurrence['count'] === 0) {
                $this->recurrence = null;
            } else {
                $this->recurrence = $recurrence;
            }

            $this->save();
        }
    }

    protected function getRecurrenceIterator(): ?RRuleIterator
    {
        $recurrence = $this->recurrence;
        $startDate = $this->due_by ?: $this->completed_at?->endOfDay();
        if ($recurrence && $startDate) {
            // The iterator will set the next date as invalid if the count is 1
            // this means the next date cannot be retrieved to set the final due
            // date. Here we increment the count by one so that final date is
            // available.
            if (isset($recurrence['count'])) {
                $recurrence['count']++;
            }

            $setter = $this->recurrence()->set;

            return new RRuleIterator($setter($recurrence), $startDate);
        }

        return null;
    }

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function (self $todo) {
            $todo->uuid = (string) Str::uuid();
        });

        static::saving(function (self $todo) {
            if ($todo->isDirty('completed_at')) {
                $todo->status = $todo->completed_at === null ? TodoStatus::NEEDS_ACTION : TodoStatus::COMPLETED;
            }
        });
        static::saved(function (self $todo) {
            if ($todo->wasChanged('completed_at') && $todo->isComplete()) {
                static::getEventDispatcher()->dispatch(new TodoCompleted($todo));
            }
        });
    }

    /**
     * @return \Planner\Database\Factories\TodoFactory
     */
    protected static function newFactory()
    {
        return TodoFactory::new();
    }
}
