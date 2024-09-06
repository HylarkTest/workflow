<?php

declare(strict_types=1);

namespace AccountIntegrations\Core\Calendar\Repositories;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use AccountIntegrations\Core\Calendar\Event;
use AccountIntegrations\Core\Calendar\Calendar;

interface CalendarRepository
{
    /**
     * @return \Illuminate\Support\Collection<int, \AccountIntegrations\Core\Calendar\Calendar>
     */
    public function getCalendars(): Collection;

    public function getCalendar(string $calendarId): Calendar;

    public function createCalendar(Calendar $calendar): Calendar;

    public function updateCalendar(Calendar $calendar): Calendar;

    public function deleteCalendar(string $calendarId): bool;

    /**
     * @return \Illuminate\Support\Collection<int, \AccountIntegrations\Core\Calendar\Event>
     */
    public function getEvents(string $calendarId, array $options = []): Collection;

    /**
     * @return \Illuminate\Support\Collection<int, \AccountIntegrations\Core\Calendar\Event>
     */
    public function getEventsBetween(string $calendarId, Carbon $start, Carbon $end, array $options = []): Collection;

    public function getEvent(string $calendarId, string $eventId): Event;

    public function createEvent(Event $event): Event;

    public function updateEvent(Event $event): Event;

    public function deleteEvent(string $calendarId, string $eventId): bool;
}
