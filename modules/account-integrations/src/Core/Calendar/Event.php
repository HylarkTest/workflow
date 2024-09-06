<?php

declare(strict_types=1);

namespace AccountIntegrations\Core\Calendar;

use Illuminate\Support\Carbon;
use AccountIntegrations\Models\IntegrationAccount;

/**
 * Using dynamic properties so we can use `property_exists` to see if they've
 * been set, because `isset` ignores `null` values.
 *
 * @property ?string $description
 * @property ?string $primaryId
 * @property ?\Illuminate\Support\Carbon $startAt
 * @property ?\Illuminate\Support\Carbon $endAt
 * @property ?array $recurrence
 * @property bool $isAllDay
 * @property ?string $recurrenceEventId
 * @property ?string $location
 * @property ?string $color
 * @property string $timezone
 */
#[\AllowDynamicProperties]
class Event
{
    public ?string $id;

    public ?string $name;

    public ?Carbon $updatedAt;

    public function __construct(array $eventArray, public Calendar $calendar, public IntegrationAccount $account)
    {
        $this->id = $eventArray['id'] ?? null;

        $this->name = $eventArray['name'] ?? null;

        $this->updatedAt = $eventArray['updatedAt'] ?? null;

        if (\array_key_exists('primaryId', $eventArray)) {
            $this->primaryId = $eventArray['primaryId'];
        }
        if (\array_key_exists('description', $eventArray)) {
            $this->description = $eventArray['description'];
        }
        if (\array_key_exists('startAt', $eventArray)) {
            $this->startAt = $eventArray['startAt'];
        }
        if (\array_key_exists('endAt', $eventArray)) {
            $this->endAt = $eventArray['endAt'];
        }
        if (\array_key_exists('recurrence', $eventArray)) {
            $this->recurrence = $eventArray['recurrence'];
        }
        if (\array_key_exists('isAllDay', $eventArray)) {
            $this->isAllDay = $eventArray['isAllDay'];
        }
        if (\array_key_exists('color', $eventArray)) {
            $this->color = $eventArray['color'];
        }
        if (\array_key_exists('timezone', $eventArray)) {
            $this->timezone = $eventArray['timezone'];
        }
        if (\array_key_exists('location', $eventArray)) {
            $this->location = $eventArray['location'];
        }
    }

    public function mainId(): string
    {
        return $this->primaryId ?? $this->id ?? '';
    }
}
