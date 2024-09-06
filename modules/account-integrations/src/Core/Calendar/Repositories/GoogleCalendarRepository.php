<?php

declare(strict_types=1);

namespace AccountIntegrations\Core\Calendar\Repositories;

use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Google\Service\Calendar\EventDateTime;
use Google\Service\Calendar as CalendarApi;
use Sabre\VObject\Property\ICalendar\Recur;
use AccountIntegrations\Core\Calendar\Event;
use AccountIntegrations\Core\GoogleRepository;
use Google\Service\Calendar\CalendarListEntry;
use AccountIntegrations\Core\Calendar\Calendar;
use Google\Service\Calendar\Event as GoogleEvent;
use AccountIntegrations\Models\IntegrationAccount;
use Google\Service\Calendar\Calendar as GoogleCalendar;
use AccountIntegrations\Exceptions\InvalidGrantException;

class GoogleCalendarRepository extends GoogleRepository implements CalendarRepository
{
    public const ICAL_DATETIME_FORMAT = 'Ymd\\THis\\Z';

    public const WEEKDAY_MAP = [
        'MO', 'TU', 'WE', 'TH', 'FR', 'SA', 'SU',
    ];

    protected CalendarApi $calendarApi;

    public function __construct(IntegrationAccount $account)
    {
        parent::__construct($account);
        $this->calendarApi = new CalendarApi($this->client);
    }

    /**
     * @return \Illuminate\Support\Collection<int, \AccountIntegrations\Core\Calendar\Calendar>
     */
    public function getCalendars(): Collection
    {
        /** @var array<int, \Google\Service\Calendar\CalendarListEntry> $calendars */
        $calendars = $this->makeRequest(fn () => $this->calendarApi->calendarList->listCalendarList());

        return collect($calendars)->map(function (CalendarListEntry $calendar) {
            return $this->buildCalendarFromGoogleCalendar($calendar);
        });
    }

    public function getCalendar(string $calendarId): Calendar
    {
        $calendar = $this->getGoogleCalendarListEntryFromId($calendarId);

        return $this->buildCalendarFromGoogleCalendar($calendar);
    }

    public function createCalendar(Calendar $calendar): Calendar
    {
        $body = $this->buildGoogleCalendarFromCalendar($calendar);
        $calendar = $this->makeRequest(
            fn () => $this->calendarApi->calendars->insert($body)
        );
        $calendarListCalendar = $this->getGoogleCalendarListEntryFromId($calendar->getId());

        return $this->buildCalendarFromGoogleCalendar($calendarListCalendar);
    }

    public function updateCalendar(Calendar $calendar): Calendar
    {
        $body = $this->buildGoogleCalendarListEntryFromCalendar($calendar);
        $googleCalendar = $this->makeRequest(
            fn () => $this->calendarApi->calendarList->update($body->getId(), $body, ['colorRgbFormat' => true]),
            Calendar::class,
            $calendar->id
        );

        return $this->buildCalendarFromGoogleCalendar($googleCalendar);
    }

    public function deleteCalendar(string $calendarId): bool
    {
        $this->getCalendar($calendarId); // Ensure calendar exists (throws exception if not found as delete method does not throw 404 error when calendar does not exist)
        $this->makeRequest(
            fn () => $this->calendarApi->calendarList->delete($this->getIdFromCalendarId($calendarId)),
            Calendar::class,
            $calendarId
        );

        return true;
    }

    /**
     * @return \Illuminate\Support\Collection<int, \AccountIntegrations\Core\Calendar\Event>
     */
    public function getEvents(string $calendarId, array $options = []): Collection
    {
        $calendar = $this->getCalendar($calendarId);
        /** @var array<int, \Google\Service\Calendar\Event> $events */
        $events = $this->makeRequest(
            fn () => $this->calendarApi->events->listEvents($this->getIdFromCalendarId($calendarId), $this->buildParamsFromOptions($options))
        );

        return collect($events)
            ->filter(fn ($event) => $event->getStart() !== null || $event->getEnd() !== null)
            ->map(fn (GoogleEvent $task) => $this->buildEventFromGoogleEvent($task, $calendar));
    }

    /**
     * @return \Illuminate\Support\Collection<int, \AccountIntegrations\Core\Calendar\Event>
     */
    public function getEventsBetween(string $calendarId, Carbon $start, Carbon $end, array $options = []): Collection
    {
        return $this->getEvents($calendarId, array_merge([
            'startsBefore' => $end,
            'endsAfter' => $start,
            'includeRecurringInstances' => true,
            'orderBy' => 'startTime',
        ], $options));
    }

    public function getEvent(string $calendarId, string $eventId): Event
    {
        $calendar = $this->getCalendar($calendarId);
        $event = $this->makeRequest(
            fn () => $this->calendarApi->events->get($this->getIdFromCalendarId($calendarId), $eventId),
            Event::class,
            $eventId
        );
        $this->checkEventCancelled($event, $eventId);

        return $this->buildEventFromGoogleEvent($event, $calendar);
    }

    public function createEvent(Event $event): Event
    {
        $body = $this->buildGoogleEventFromEvent($event);
        $googleEvent = $this->makeRequest(
            fn () => $this->calendarApi->events->insert($event->calendar->baseId(), $body),
            Event::class,
            $event->id
        );

        return $this->buildEventFromGoogleEvent($googleEvent, $event->calendar);
    }

    public function updateEvent(Event $event): Event
    {
        $body = $this->buildGoogleEventFromEvent($event);
        $googleEvent = $this->makeRequest(
            fn () => $this->calendarApi->events->patch($event->calendar->baseId(), $event->id, $body),
            Event::class,
            $event->id
        );
        $this->checkEventCancelled($googleEvent, (string) $event->id);

        return $this->buildEventFromGoogleEvent($googleEvent, $event->calendar);
    }

    /**
     * @throws InvalidGrantException
     * @throws \Exception
     */
    public function deleteEvent(string $calendarId, string $eventId): bool
    {
        $this->makeRequest(
            fn () => $this->calendarApi->events->delete($this->getIdFromCalendarId($calendarId), $eventId),
            Event::class,
            $eventId
        );

        return true;
    }

    protected function getIdFromCalendarId(?string $calendarId): string
    {
        if (! $calendarId) {
            return '';
        }

        return Str::startsWith($calendarId, $this->account->account_name.'::')
            ? mb_substr($calendarId, mb_strlen($this->account->account_name) + 2)
            : $calendarId;
    }

    protected function getGoogleCalendarListEntryFromId(string $calendarId): CalendarListEntry
    {
        return $this->makeRequest(
            fn () => $this->calendarApi->calendarList->get($this->getIdFromCalendarId($calendarId)),
            Calendar::class,
            $calendarId
        );
    }

    protected function buildGoogleEventFromEvent(Event $event): GoogleEvent
    {
        $googleEvent = new GoogleEvent;
        if ($event->id) {
            $googleEvent->setId($event->id);
        }
        $googleEvent->setSummary($event->name);
        if (property_exists($event, 'description')) {
            $googleEvent->setDescription($event->description);
        }
        if (property_exists($event, 'startAt')) {
            $start = [];
            if (property_exists($event, 'timezone')) {
                $start['timeZone'] = $event->timezone;
            }
            if (property_exists($event, 'isAllDay') && $event->isAllDay) {
                $start['date'] = $event->startAt?->format('Y-m-d');
            } else {
                $start['dateTime'] = $event->startAt?->toRfc3339String();
            }
            $googleEvent->setStart(new EventDateTime($start));
        }

        if (property_exists($event, 'endAt')) {
            $end = [];
            if (property_exists($event, 'timezone')) {
                $end['timeZone'] = $event->timezone;
            }
            if (property_exists($event, 'isAllDay') && $event->isAllDay) {
                $end['date'] = $event->endAt?->format('Y-m-d');
            } else {
                $end['dateTime'] = $event->endAt?->toRfc3339String();
            }
            $googleEvent->setEnd(new EventDateTime($end));
        }

        if (property_exists($event, 'recurrence') && $event->recurrence) {
            $googleEvent->setRecurrence([$this->recurrenceToRule($event->recurrence)]);
        }

        return $googleEvent;
    }

    protected function recurrenceToRule(array $recurrence): string
    {
        return 'RRULE:'.collect($recurrence)
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

    protected function ruleToRecurrence(string $rule): ?array
    {
        if (! str_starts_with($rule, 'RRULE:')) {
            return null;
        }
        $recurrence = Recur::stringToArray(mb_substr($rule, 6));

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

    protected function buildGoogleCalendarFromCalendar(Calendar $calendar): GoogleCalendar
    {
        $googleCalendar = new GoogleCalendar;
        $googleCalendar->setSummary($calendar->name);

        return $googleCalendar;
    }

    protected function buildGoogleCalendarListEntryFromCalendar(Calendar $calendar): CalendarListEntry
    {
        $googleCalendar = new CalendarListEntry;
        $googleCalendar->setSummary($calendar->name);
        if ($calendar->id) {
            $googleCalendar->setId($calendar->baseId());
        }
        if (isset($calendar->color)) {
            $googleCalendar->setBackgroundColor($calendar->color);
            $googleCalendar->setForegroundColor('#000000');
        }

        return $googleCalendar;
    }

    protected function buildCalendarFromGoogleCalendar(CalendarListEntry $googleCalendar): Calendar
    {
        $isOwner = $googleCalendar->getAccessRole() === 'owner';
        $isReadOnly = $this->googleCalendarIsReadOnly($googleCalendar);

        return new Calendar(
            [
                'id' => $googleCalendar->getId() ? $this->account->account_name.'::'.$googleCalendar->getId() : '',
                'name' => $googleCalendar->getSummary() ?: '',
                'color' => $googleCalendar->getBackgroundColor(),
                'isOwner' => $isOwner,
                'isShared' => ! $isOwner,
                'isDefault' => $googleCalendar->getPrimary(),
                'isReadOnly' => $isReadOnly,
            ],
            $this->account,
        );
    }

    protected function googleCalendarIsReadOnly(CalendarListEntry $googleCalendar): bool
    {
        return ! \in_array($googleCalendar->getAccessRole(), ['owner', 'writer'], true);
    }

    protected function buildEventFromGoogleEvent(GoogleEvent $googleEvent, Calendar $calendar): Event
    {
        $startDateTime = $googleEvent->getStart();
        $endDateTime = $googleEvent->getEnd();

        $isAllDay = $startDateTime !== null && $startDateTime->getDate();
        if ($isAllDay) {
            $startAt = Carbon::parse($startDateTime->getDate());
            $endAt = Carbon::parse($endDateTime->getDate());
        } else {
            $startAt = Carbon::parse($startDateTime->getDateTime(), $startDateTime->getTimeZone())->utc();
            $endAt = Carbon::parse($endDateTime->getDateTime(), $endDateTime->getTimeZone())->utc();
        }

        $recurrence = $googleEvent->getRecurrence()[0] ?? null;

        return new Event([
            'id' => $googleEvent->getId(),
            'primaryId' => $googleEvent->getRecurringEventId() ?: $googleEvent->getId(),
            'name' => $googleEvent->getSummary(),
            'updatedAt' => Carbon::parse($googleEvent->getUpdated()),
            'description' => $googleEvent->getDescription(),
            'isAllDay' => (bool) $startDateTime->getDate(),
            'startAt' => $startAt,
            'endAt' => $endAt,
            'timezone' => $startDateTime->getTimeZone(),
            'recurrence' => $recurrence ? $this->ruleToRecurrence($recurrence) : null,
            'recurringEventId' => $googleEvent->getRecurringEventId(),
            'location' => $googleEvent->getLocation(),
        ], $calendar, $this->account);
    }

    protected function buildParamsFromOptions(array $options): array
    {
        $query = [];

        $query['maxResults'] = $options['first'] ?? 25;

        if (isset($options['page'])) {
            $query['pageToken'] = $options['page'];
        }

        if (isset($options['startsBefore'])) {
            $query['timeMax'] = $options['startsBefore']->toRfc3339String();
        }
        if (isset($options['endsAfter'])) {
            $query['timeMin'] = $options['endsAfter']->toRfc3339String();
        }
        if (isset($options['includeRecurringInstances'])) {
            $query['singleEvents'] = $options['includeRecurringInstances'];
        }
        if (isset($options['orderBy'])) {
            $query['orderBy'] = $options['orderBy'];
        }

        return $query;
    }
}
