<?php

declare(strict_types=1);

namespace App\Models;

use GraphQL\Deferred;
use Planner\Models\Event as BaseEvent;
use App\Models\Contracts\FeatureListItem;
use App\Models\Concerns\HasFeatureListItemMethods;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Event
 *
 * @property \App\Models\Calendar $calendar
 *
 * @method \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Calendar, \App\Models\Event> calendar()
 *
 * @implements \App\Models\Contracts\FeatureListItem<\App\Models\Calendar, \App\Models\Event>
 */
class Event extends BaseEvent implements FeatureListItem
{
    /** @use \App\Models\Concerns\HasFeatureListItemMethods<\App\Models\Calendar> */
    use HasFeatureListItemMethods;

    protected array $actionIgnoredColumns = [
        'uuid',
    ];

    public function globalInstanceId(): string
    {
        $id = $this->globalId();
        if ($this->isInstance) {
            return $id.'_'.$this->instanceId();
        }

        return $id;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Calendar, \App\Models\Event>
     */
    public function list(): BelongsTo
    {
        return $this->calendar();
    }

    public static function formatCalendarIdActionPayload(?int $calendarId): ?Deferred
    {
        return static::formatListIdActionPayload($calendarId);
    }

    public static function formatIsAllDayActionPayload(?bool $allDay): null
    {
        return null;
    }

    protected function secondarySearchableArray(): array
    {
        return array_merge([
            [
                'text' => $this->description,
                'map' => 'description',
            ],
        ], $this->getAssigneesMappedForFinder());
    }
}
