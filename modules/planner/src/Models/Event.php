<?php

declare(strict_types=1);

namespace Planner\Models;

use Carbon\CarbonTimeZone;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Planner\Core\EventStatus;
use Illuminate\Support\Carbon;
use Sabre\VObject\Component\VCalendar;
use Sabre\VObject\Recur\RRuleIterator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Sabre\VObject\Property\ICalendar\Recur;
use Carbon\Exceptions\InvalidTimeZoneException;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Attributes
 *
 * @property int $id
 * @property string $uuid
 * @property int $calendar_id
 * @property string $name
 * @property string|null $description
 * @property \Planner\Core\EventStatus $status
 * @property \Illuminate\Support\Carbon $start_at
 * @property \Illuminate\Support\Carbon $end_at
 * @property string $timezone
 * @property string[] $attendees
 * @property RecurrenceArray|null $recurrence
 * @property \Illuminate\Support\Carbon|null $repeat_until
 * @property int $priority
 * @property bool $is_all_day
 * @property string|null $location
 * @property \Illuminate\Support\Carbon|null $remind_at
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 *
 * Accessors
 * @property string $recurrenceRule
 *
 * Relationships
 * @property \Planner\Models\Calendar $calendar
 *
 * @phpstan-type RecurrenceArray array{
 *     frequency: string,
 *     interval: int,
 *     byDay: string[]|null,
 *     count?: int|null,
 *     until?: \Illuminate\Support\Carbon|null
 * }
 * @phpstan-type EventData array{
 *     id?: int,
 *     uuid?: string,
 *     calendar_id?: int,
 *     name?: string,
 *     description?: string,
 *     status?: \Planner\Core\EventStatus|string,
 *     start_at?: string,
 *     end_at?: string,
 *     timezone?: string,
 *     attendees?: string[],
 *     recurrence?: RecurrenceArray,
 *     repeat_until?: string|null,
 *     priority?: int,
 *     is_all_day?: bool,
 *     location?: string,
 *     remind_at?: string|null,
 * }
 */
class Event extends Model
{
    use HasFactory;

    public const ICAL_DATETIME_FORMAT = 'Ymd\\THis\\Z';

    public const ICAL_DATE_FORMAT = 'Ymd';

    public const WEEKDAY_MAP = [
        'MO', 'TU', 'WE', 'TH', 'FR', 'SA', 'SU',
    ];

    public bool $isInstance = false;

    /**
     * Get the fillable attributes for the model.
     */
    public function getFillable(): array
    {
        $fillable = parent::getFillable();

        return array_merge($fillable, [
            'name',
            'start_at',
            'end_at',
            'is_all_day',
            'timezone',
            'location',
            'priority',
            'recurrence',
            'recurrence_rule',
            'repeat_until',
            'description',
            'remind_at',
            'status',
        ]);
    }

    /**
     * @return array<string, string>
     */
    public function getCasts(): array
    {
        $casts = parent::getCasts();

        return array_merge($casts, [
            'status' => EventStatus::class,
            'attendees' => 'array',
            'is_all_day' => 'boolean',
            'start_at' => 'datetime',
            'end_at' => 'datetime',
            'repeat_until' => 'datetime',
            'remind_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ]);
    }

    public function instanceId(): ?string
    {
        return $this->isInstance ? $this->start_at->format('Ymd\THis\Z') : null;
    }

    /**
     * @template TKey of array-key
     *
     * @param  array<TKey, \Planner\Models\Event>  $models
     * @return \Planner\Models\EventCollection<TKey>
     */
    public function newCollection(array $models = []): EventCollection
    {
        return new EventCollection($models);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Casts\Attribute<string, string>
     */
    public function startDate(): Attribute
    {
        return $this->buildDateAttribute();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Casts\Attribute<string, string>
     */
    public function endDate(): Attribute
    {
        return $this->buildDateAttribute();
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
        return Attribute::get(fn ($_, $attributes = []): ?string => $attributes['recurrence_rule'] ?? null);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Casts\Attribute<\Carbon\CarbonTimeZone, string>
     */
    public function timezone(): Attribute
    {
        return Attribute::make(
            get: fn (string $timezone) => CarbonTimeZone::create($timezone),
            set: function (string|CarbonTimeZone $timezone) {
                if (\is_string($timezone)) {
                    $parsedTimezone = CarbonTimeZone::create($timezone);
                    if (! $parsedTimezone) {
                        throw new InvalidTimeZoneException('Unknown or bad timezone ('.$timezone.')');
                    }
                    $timezone = $parsedTimezone;
                }

                return $timezone->getName();
            }
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Casts\Attribute<RecurrenceArray|null, string|RecurrenceArray|null>
     */
    public function recurrence(): Attribute
    {
        return new Attribute(
            get: function (): ?array {
                $recurrenceRule = $this->recurrenceRule;
                if ($recurrenceRule) {
                    $recurrence = Recur::stringToArray($recurrenceRule);

                    $byDay = $recurrence['BYDAY'] ?? null;
                    if (\is_string($byDay)) {
                        $byDay = explode(',', $byDay);
                    }

                    if ($until = $recurrence['UNTIL'] ?? null) {
                        $until = Carbon::create($until);
                    }

                    return [
                        'frequency' => $recurrence['FREQ'],
                        'interval' => $recurrence['INTERVAL'] ?? 1,
                        'byDay' => $byDay,
                        'count' => $recurrence['COUNT'] ?? null,
                        'until' => $until,
                    ];
                }

                return null;
            },
            set: function (string|array|null $recurrence): array {
                if (\is_array($recurrence)) {
                    $recurrence = collect($recurrence)
                        ->filter()
                        ->map(function ($value, string $key) {
                            if (mb_strtolower($key) === 'frequency') {
                                $key = 'FREQ';
                            }
                            $key = mb_strtoupper($key);

                            if ($key === 'BYDAY') {
                                $value = array_map(
                                    static fn (string|int $day): string => \is_string($day) ? $day : self::WEEKDAY_MAP[$day],
                                    (array) $value
                                );
                            }

                            if ($key === 'UNTIL') {
                                $value = Carbon::parse($value)->format(self::ICAL_DATETIME_FORMAT);
                            }

                            return "$key=".(\is_array($value) ? implode(',', $value) : $value);
                        })
                        ->join(';');
                }

                return ['recurrence_rule' => $recurrence ?: null];
            }
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Planner\Models\Calendar, \Planner\Models\Event>
     */
    public function calendar(): BelongsTo
    {
        /** @var class-string<\Planner\Models\Calendar> $model */
        $model = config('planner.models.calendar');

        return $this->belongsTo($model, (new $model)->getForeignKey());
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
        if ($this->is_all_day) {
            $props['DTSTART'] = $this->start_at->format(self::ICAL_DATE_FORMAT);
            $props['DTEND'] = $this->end_at->format(self::ICAL_DATE_FORMAT);
        } else {
            $props['DTSTART'] = $this->start_at->format(self::ICAL_DATETIME_FORMAT);
            $props['DTEND'] = $this->end_at->format(self::ICAL_DATETIME_FORMAT);
            $props['DURATION'] = $this->end_at->diffAsCarbonInterval($this->start_at)->spec();
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

        return new VCalendar(['VTODO' => $props]);
    }

    public function isInfinite(): bool
    {
        return (bool) $this->getRecurrenceIterator()?->isInfinite();
    }

    public function intersectsRange(Carbon $from, Carbon $to): bool
    {
        return $this->startsBefore($to) && $this->endsAfter($from);
    }

    public function endsAfter(Carbon $date): bool
    {
        return $this->end_at->isAfter($date);
    }

    public function startsBefore(Carbon $date): bool
    {
        return $this->start_at->isBefore($date);
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<\Planner\Models\Event>  $query
     */
    public function scopeInRange(Builder $query, Carbon $from, ?Carbon $to, bool $includeRecurringInstances = true): void
    {
        // If we are fetching the recurring instances then we need to filter
        // all events that start before the end of the range and end after
        // the beginning of the range, as well as fetching all recurring
        // events that start before the start of the range and end after the
        // start of the range.
        $query->where(function (Builder $query) use ($from, $to, $includeRecurringInstances) {
            $query->where(function (Builder $query) use ($from, $to) {
                $query->endsAfter($from);
                if ($to) {
                    $query->startsBefore($to);
                }
            })->when($includeRecurringInstances, function (Builder $query) use ($from) {
                $query->orWhere(function (Builder $query) use ($from) {
                    $query->startsBefore($from)
                        ->whereNotNull('repeat_until')
                        ->where('repeat_until', '>', $from);
                });
            });
        });
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<\Planner\Models\Event>  $query
     */
    public function scopeStartsBefore(Builder $query, Carbon $date): void
    {
        $query->where('start_at', '<', $date);
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<\Planner\Models\Event>  $query
     */
    public function scopeEndsAfter(Builder $query, Carbon $date): void
    {
        $query->where('end_at', '>', $date);
    }

    public function getRecurrenceIterator(): ?RRuleIterator
    {
        $recurrence = $this->recurrenceRule;
        $startDate = $this->start_at;

        return $recurrence ? new RRuleIterator($recurrence, $startDate) : null;
    }

    /**
     * @param  EventData  $data
     */
    public function updateInstance(string $instanceId, array $data, bool $thisAndFuture): static
    {
        $recurrence = $this->recurrence;
        $rule = $this->recurrenceRule;
        $eventAtTz = $this->newInstanceAtTz();
        [$iterator, $previous, $count] = $eventAtTz->getIteratorAdvancedToInstance($instanceId);
        /** @var \Sabre\VObject\Recur\RRuleIterator $iterator */
        $instance = clone $this;
        $instance->fill($data);
        if ($previous) {
            $instance->setRawAttributes(Arr::except($instance->getAttributes(), 'id'));

            $changedAttributes = Arr::except($instance->getAttributes(), ['id', 'calendar_id']);
            $originalAttributes = Arr::except($this->getAttributes(), ['id', 'calendar_id']);

            if ($changedAttributes === $originalAttributes) {
                return $instance;
            }

            if (! isset($data['start_at'])) {
                $instance->start_at = $iterator->current();
            }
            if (! isset($data['end_at'])) {
                $instance->end_at = $iterator->current()?->add($this->end_at->diff($this->start_at, true));
            }
            $instance->exists = false;

            $this->recurrence = $eventAtTz->getRecurrenceUpToInstance($recurrence, $count, $previous);
            $this->save();
        }
        if ($instance->recurrenceRule !== $rule) {
            $thisAndFuture = true;
        }

        $iterator->next();
        if ($iterator->valid()) {
            if ($thisAndFuture) {
                if (! isset($data['recurrence'])) {
                    $instance->recurrence = $eventAtTz->getRecurrenceFromCount($recurrence, $count);
                }
                $instance = $instance->newInstanceAtTz('UTC', true);
                $instance->save();
            } else {
                $nextInstance = clone $this;
                $instance->recurrence = null;
                $nextInstance->recurrence = $eventAtTz->getRecurrenceFromCount($recurrence, $count + 1);
                $instanceDate = $iterator->current();
                $nextInstance->start_at = $instanceDate;
                $nextInstance->end_at = $instanceDate->add($this->end_at->diff($this->start_at, true));
                $nextInstance->setRawAttributes(Arr::except($nextInstance->getAttributes(), 'id'));
                $nextInstance->exists = false;
                $instance = $instance->newInstanceAtTz('UTC', true);
                $instance->save();
                $nextInstance->newInstanceAtTz('UTC', true)->save();
            }
        } else {
            $instance->id = $this->id;
        }

        return $instance;
    }

    public function getInstance(string $instanceId): static
    {
        $eventAtTz = $this->newInstanceAtTz();
        [$iterator] = $eventAtTz->getIteratorAdvancedToInstance($instanceId);
        $instance = clone $this;
        $instance->start_at = $iterator?->current();
        $instance->end_at = $iterator?->current()?->add($this->end_at->diff($this->start_at, true));
        $instance->exists = false;
        $instance->isInstance = true;

        return $instance;
    }

    public function deleteInstance(string $instanceId, bool $thisAndFuture, bool $force = false): bool
    {
        $recurrence = $this->recurrence;
        $eventWithTz = $this->newInstanceAtTz();
        [$iterator, $previous, $count] = $eventWithTz->getIteratorAdvancedToInstance($instanceId);
        $nextInstance = clone $this;
        /** @var \Sabre\VObject\Recur\RRuleIterator $iterator */
        if (! $count) {
            if ($force) {
                $this->forceDelete();
            } else {
                $this->delete();
            }
            if ($thisAndFuture) {
                return true;
            }
        } else {
            $this->recurrence = $eventWithTz->getRecurrenceUpToInstance($recurrence, $count, $previous ?? $iterator->current());
            $this->save();
        }

        if (! $thisAndFuture) {
            $iterator->next();
            if ($iterator->valid()) {
                $nextInstance->recurrence = $eventWithTz->getRecurrenceFromCount($recurrence, $count + 1);
                $instanceDate = $iterator->current();
                $nextInstance->start_at = $instanceDate;
                $nextInstance->end_at = $instanceDate->add($this->end_at->diff($this->start_at, true));
                $nextInstance->setRawAttributes(Arr::except($nextInstance->getAttributes(), 'id'));
                $nextInstance->exists = false;
                $nextInstance->newInstanceAtTz('UTC', true)->save();
            }
        }

        return true;
    }

    public function newInstanceAtTz(?string $tz = null, bool $fromOriginal = false): static
    {
        $tz = $tz ?? $this->timezone;
        $instance = clone $this;
        $start = $instance->start_at;
        $end = $instance->end_at;
        if ($fromOriginal) {
            $start->shiftTimezone($this->timezone);
            $end->shiftTimezone($this->timezone);
        }
        $instance->start_at = $start->setTimezone($tz);
        $instance->end_at = $end->setTimezone($tz);

        return $instance;
    }

    /**
     * @return array{
     *     0: \Sabre\VObject\Recur\RRuleIterator|null,
     *     1: \Illuminate\Support\Carbon|null,
     *     2: int,
     * }
     */
    protected function getIteratorAdvancedToInstance(string $instanceId): array
    {
        $iterator = $this->getRecurrenceIterator();
        /** @var \Illuminate\Support\Carbon $timeOfInstance */
        $timeOfInstance = Carbon::createFromFormat('Ymd\THis\Z', $instanceId);
        $timeOfInstance->setTimezone($this->timezone);

        [$previous, $count] = $this->advanceIteratorToInstance($iterator, $timeOfInstance);

        return [$iterator, $previous, $count];
    }

    /**
     * @param  RecurrenceArray|null  $originalRecurrence
     * @return RecurrenceArray|null
     */
    protected function getRecurrenceFromCount(?array $originalRecurrence, int $count): ?array
    {
        if ($originalRecurrence['count'] ?? false) {
            $count = $originalRecurrence['count'] - $count;
            if ($count <= 1) {
                return null;
            }
            $originalRecurrence['count'] = $count;
        }

        return $originalRecurrence;
    }

    /**
     * @param  RecurrenceArray|null  $originalRecurrence
     * @return RecurrenceArray|null
     */
    protected function getRecurrenceUpToInstance(?array $originalRecurrence, int $count, ?Carbon $previous = null): ?array
    {
        if (is_array($originalRecurrence)) {
            if ($originalRecurrence['count'] ?? false) {
                if ($count <= 1) {
                    return null;
                }
                $originalRecurrence['count'] = $count;
            } else {
                if ($this->start_at->equalTo($previous)) {
                    return null;
                }
                $originalRecurrence['until'] = $previous;
            }
        }

        return $originalRecurrence;
    }

    /**
     * @param  false|\Illuminate\Support\Carbon  $timeOfInstance
     * @return array{ 0: \Illuminate\Support\Carbon|null, 1: int }
     */
    protected function advanceIteratorToInstance(?RRuleIterator $iterator, bool|Carbon $timeOfInstance): array
    {
        if (! $iterator || ! $timeOfInstance) {
            throw new ModelNotFoundException('The event does not have an instance for the specified ID');
        }
        $count = 0;
        while ($iterator->valid() && $timeOfInstance->greaterThan($iterator->current())) {
            $previous = $iterator->current();
            $iterator->next();
            $count++;
        }
        if (! $iterator->valid()) {
            throw new ModelNotFoundException('The event does not have an instance for the specified ID');
        }

        return [$previous ?? null, $count];
    }

    /**
     * @return \Illuminate\Database\Eloquent\Casts\Attribute<string, string>
     */
    protected function buildDateAttribute()
    {
        return Attribute::make(
            get: fn (string $date) => Carbon::rawParse($date)->shiftTimezone($this->timezone),
            set: function (string|Carbon $date) {
                if (\is_string($date)) {
                    $date = Carbon::rawParse($date);
                }
                $date->setTimezone($this->timezone);

                return $date->utc()->format('Y-m-d H:i:s');
            }
        );
    }

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function (self $event) {
            $event->uuid = (string) Str::uuid();
        });

        static::saving(function (self $event) {
            if ($iterator = $event->getRecurrenceIterator()) {
                if ($iterator->isInfinite()) {
                    $event->repeat_until = Carbon::parse('9999-12-31 23:59:59');
                } else {
                    $lastDate = null;
                    while ($iterator->valid()) {
                        $lastDate = $iterator->current();
                        $iterator->next();
                    }
                    if ($lastDate) {
                        $event->repeat_until = $lastDate;
                    }
                }
            } else {
                $event->repeat_until = null;
            }
        });
    }
}
