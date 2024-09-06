<?php

declare(strict_types=1);

namespace AccountIntegrations\Core\Calendar\Repositories;

use Exception;
use MarkupUtils\HTML;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Microsoft\Graph\Model\BodyType;
use Microsoft\Graph\Model\ItemBody;
use Microsoft\Graph\Model\Location;
use GuzzleHttp\Promise\PromiseInterface;
use Microsoft\Graph\Model\RecurrenceRange;
use Microsoft\Graph\Model\DateTimeTimeZone;
use AccountIntegrations\Core\Calendar\Event;
use Microsoft\Graph\Model\RecurrencePattern;
use Microsoft\Graph\Model\PatternedRecurrence;
use Microsoft\Graph\Model\RecurrenceRangeType;
use AccountIntegrations\Core\Calendar\Calendar;
use Microsoft\Graph\Model\RecurrencePatternType;
use AccountIntegrations\Models\IntegrationAccount;
use Microsoft\Graph\Model\Event as MicrosoftEvent;
use AccountIntegrations\Core\MicrosoftGraphGateway;
use Microsoft\Graph\Model\Calendar as MicrosoftCalendar;

class MicrosoftCalendarRepository implements CalendarRepository
{
    protected MicrosoftGraphGateway $gateway;

    public function __construct(protected IntegrationAccount $account)
    {
        $this->gateway = new MicrosoftGraphGateway($account);
    }

    /**
     * @return \Illuminate\Support\Collection<int, \AccountIntegrations\Core\Calendar\Calendar>
     */
    public function getCalendars(): Collection
    {
        /*
         * The order that these are returned in is a bit confusing.
         * - First comes the calendars that have been shared with the user
         *   in alphabetical order (ignoring case).
         * - Then comes the default "Tasks" calendar.
         * - After that comes all the calendars owned by the user in alphabetical
         *   order (ignoring case).
         */
        $calendars = $this->gateway->getCollection(
            '/me/calendars',
            MicrosoftCalendar::class,
            '',
            MicrosoftCalendar::class
        );

        return collect($calendars)->map(function (MicrosoftCalendar $calendar) {
            return $this->buildCalendarFromMicrosoftCalendar($calendar);
        });
    }

    /**
     * @throws \Microsoft\Graph\Exception\GraphException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \JsonException
     */
    public function createCalendar(Calendar $calendar): Calendar
    {
        $response = $this->gateway->createItem(
            '/me/calendars',
            $this->buildMicrosoftCalendarFromCalendar($calendar),
            MicrosoftCalendar::class,
            (string) $calendar->id
        );

        return $this->buildCalendarFromMicrosoftCalendar($response);
    }

    /**
     * @throws \Microsoft\Graph\Exception\GraphException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \JsonException
     */
    public function updateCalendar(Calendar $calendar): Calendar
    {
        $calendarId = $this->getCalendarId($calendar->id);

        /** @var \Microsoft\Graph\Model\Calendar $oldCalendar */
        $oldCalendar = $this->gateway->handleWaitPromise(
            $this->getCalendarPromise($calendarId),
            MicrosoftCalendar::class,
            $calendarId
        );

        if ($oldCalendar->getIsDefaultCalendar()) {
            unset($calendar->name);
        }
        $newCalendar = $this->buildMicrosoftCalendarFromCalendar($calendar);
        $response = $this->gateway->updateItem(
            "/me/calendars/$calendarId",
            $newCalendar,
            MicrosoftCalendar::class,
            $calendarId
        );

        return $this->buildCalendarFromMicrosoftCalendar($response);
    }

    /**
     * @throws \Exception
     */
    public function deleteCalendar(string $calendarId): bool
    {
        $calendarId = $this->getCalendarId($calendarId);

        return $this->gateway->deleteItem(
            "/me/calendars/$calendarId",
            MicrosoftCalendar::class,
            $calendarId
        );
    }

    /**
     * @return \Illuminate\Support\Collection<int, \AccountIntegrations\Core\Calendar\Event>
     */
    public function getEvents(string $calendarId, array $options = []): Collection
    {
        $calendarId = $this->getCalendarId($calendarId);
        $events = $this->gateway->getCollection(
            $this->buildUrlFromOptions("/me/calendars/$calendarId/events", $options),
            MicrosoftCalendar::class,
            $calendarId,
            MicrosoftEvent::class
        );

        $calendar = $this->getCalendar($calendarId);

        return collect($events)->map(function (MicrosoftEvent $task) use ($calendar) {
            return $this->buildEventFromMicrosoftEvent($task, $calendar);
        });
    }

    /**
     * @return \Illuminate\Support\Collection<int, \AccountIntegrations\Core\Calendar\Event>
     */
    public function getEventsBetween(string $calendarId, Carbon $start, Carbon $end, array $options = []): Collection
    {
        $calendarId = $this->getCalendarId($calendarId);
        $startString = urlencode($start->toIso8601String());
        $endString = urlencode($end->toIso8601String());

        $events = $this->gateway->getCollection(
            $this->buildUrlFromOptions(
                "/me/calendars/$calendarId/calendarView?startDateTime={$startString}&endDateTime={$endString}",
                $options
            ),
            MicrosoftCalendar::class,
            $calendarId,
            MicrosoftEvent::class
        );

        $calendar = $this->getCalendar($calendarId);

        return collect($events)->map(function (MicrosoftEvent $task) use ($calendar) {
            return $this->buildEventFromMicrosoftEvent($task, $calendar);
        });
    }

    public function getEvent(string $calendarId, string $eventId): Event
    {
        $calendarId = $this->getCalendarId($calendarId);
        $calendar = $this->getCalendar($calendarId);

        /** @var \Microsoft\Graph\Model\Event $event */
        $event = $this->gateway->getItem(
            "/me/calendars/$calendarId/events/$eventId",
            MicrosoftEvent::class,
            $eventId,
            MicrosoftEvent::class
        );

        return $this->buildEventFromMicrosoftEvent($event, $calendar);
    }

    /**
     * @throws \Microsoft\Graph\Exception\GraphException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \JsonException
     * @throws \Exception
     */
    public function createEvent(Event $event): Event
    {
        $calendarId = $this->getCalendarId($event->calendar->id);
        $microsoftEvent = $this->buildMicrosoftEventFromEvent($event);
        $response = $this->gateway->createItem(
            '/me/calendars/'.$calendarId.'/events',
            $microsoftEvent,
            MicrosoftEvent::class,
            (string) $event->id
        );

        return $this->buildEventFromMicrosoftEvent($response, $event->calendar);
    }

    /**
     * @throws \Microsoft\Graph\Exception\GraphException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \JsonException
     * @throws \Exception
     */
    public function updateEvent(Event $event): Event
    {
        $calendarId = $this->getCalendarId($event->calendar->id);
        $microsoftEvent = $this->buildMicrosoftEventFromEvent($event);
        $response = $this->gateway->updateItem(
            '/me/calendars/'.$calendarId.'/events/'.$event->id,
            $microsoftEvent,
            MicrosoftEvent::class,
            (string) $event->id
        );

        return $this->buildEventFromMicrosoftEvent($response, $event->calendar);
    }

    /**
     * @throws \Exception
     */
    public function deleteEvent(string $calendarId, string $eventId): bool
    {
        $calendarId = $this->getCalendarId($calendarId);

        return $this->gateway->deleteItem(
            "/me/calendars/$calendarId/events/$eventId",
            MicrosoftEvent::class,
            $eventId
        );
    }

    public function getCalendar(string $calendarId): Calendar
    {
        $calendarId = $this->getCalendarId($calendarId);
        $promise = $this->getCalendarPromise($calendarId);
        $calendar = $this->gateway->handleWaitPromise($promise, Calendar::class, $calendarId);

        return $this->buildCalendarFromMicrosoftCalendar($calendar);
    }

    protected function getCalendarPromise(string $calendarId): PromiseInterface
    {
        return $this->gateway->getItemAsync("/me/calendars/$calendarId", MicrosoftCalendar::class);
    }

    /**
     * @throws \Microsoft\Graph\Exception\GraphException
     */
    protected function buildMicrosoftEventFromEvent(Event $event): MicrosoftEvent
    {
        $microsoftEvent = new MicrosoftEvent;
        $isAllDay = $event->isAllDay ?? false;
        if ($event->id) {
            $microsoftEvent->setId($event->id);
        }
        if (isset($event->name)) {
            $microsoftEvent->setSubject($event->name);
        }
        if (property_exists($event, 'description') && $event->description) {
            $microsoftEvent->setBody(
                (new ItemBody)
                    ->setContent($event->description)
                    ->setContentType(new BodyType(BodyType::TEXT))
            );
        }
        if (property_exists($event, 'startAt')) {
            $startAt = $event->startAt;
            if ($isAllDay && $startAt) {
                $startAt->startOfDay();
            }
            /** @phpstan-ignore-next-line Microsoft class is not typed properly, need to set null to unset */
            $microsoftEvent->setStart($startAt ? new DateTimeTimeZone([
                'dateTime' => $startAt->toISOString(),
                'timeZone' => $startAt->timezone->getName(),
            ]) : null);
        }
        if (property_exists($event, 'endAt')) {
            $endAt = $event->endAt;
            if ($isAllDay && $endAt) {
                $endAt->addDay()->startOfDay();
            }
            /** @phpstan-ignore-next-line Microsoft class is not typed properly, need to set null to unset */
            $microsoftEvent->setEnd($endAt ? new DateTimeTimeZone([
                'dateTime' => $endAt->toISOString(),
                'timeZone' => $endAt->timezone->getName(),
            ]) : null);
        }
        if (property_exists($event, 'recurrence') && $event->recurrence) {
            /** @phpstan-ignore-next-line Microsoft class is not typed properly, need to set null to unset */
            $microsoftEvent->setRecurrence($this->formatRecurrenceArray($event->recurrence, $event->startAt));
        }
        if (property_exists($event, 'isAllDay')) {
            $microsoftEvent->setIsAllDay($event->isAllDay);
        }
        if (property_exists($event, 'location')) {
            /** @phpstan-ignore-next-line Microsoft class is not typed properly, need to set null to unset */
            $microsoftEvent->setLocation($event->location ? (new Location)->setDisplayName($event->location) : null);
        }

        return $microsoftEvent;
    }

    protected function buildMicrosoftCalendarFromCalendar(Calendar $calendar): MicrosoftCalendar
    {
        $microsoftCalendar = new MicrosoftCalendar;
        if (isset($calendar->name)) {
            $microsoftCalendar->setName($calendar->name);
        }
        if ($calendar->id) {
            $microsoftCalendar->setId($calendar->id);
        }
        if (isset($calendar->color)) {
            $microsoftCalendar->setHexColor($calendar->color);
        }

        return $microsoftCalendar;
    }

    protected function buildCalendarFromMicrosoftCalendar(MicrosoftCalendar $microsoftCalendar): Calendar
    {
        $isOwner = $microsoftCalendar->getOwner()?->getAddress() === $this->account->account_name;

        return new Calendar(
            [
                'id' => $microsoftCalendar->getId() ? $this->account->account_name.'::'.$microsoftCalendar->getId() : '',
                'name' => $microsoftCalendar->getName() ?: '',
                'color' => $microsoftCalendar->getHexColor(),
                'isOwner' => $isOwner,
                'isShared' => ! $isOwner,
                'isDefault' => $microsoftCalendar->getIsDefaultCalendar(),
                'isReadOnly' => ! $microsoftCalendar->getCanEdit(),
            ],
            $this->account,
        );
    }

    /**
     * @throws \Exception
     */
    protected function buildEventFromMicrosoftEvent(MicrosoftEvent $microsoftEvent, Calendar $calendar): Event
    {
        $recurrence = $microsoftEvent->getRecurrence();
        $startDateTime = $microsoftEvent->getStart();
        $endDateTime = $microsoftEvent->getEnd();

        $body = $microsoftEvent->getBody();
        if ($body) {
            $content = $body->getContent();
            $description = $body->getContentType()?->is(BodyType::HTML)
                ? (new HTML($content ?: ''))->convertToPlaintext()
                : $content;
        } else {
            $description = null;
        }

        return new Event([
            'id' => $microsoftEvent->getId(),
            'primaryId' => $microsoftEvent->getSeriesMasterId() ?? $microsoftEvent->getId(),
            'name' => $microsoftEvent->getSubject(),
            'updatedAt' => Carbon::parse($microsoftEvent->getLastModifiedDateTime()),
            'description' => $description,
            'startAt' => $startDateTime ? Carbon::parse($startDateTime->getDateTime(), $startDateTime->getTimeZone()) : null,
            'endAt' => $endDateTime ? Carbon::parse($endDateTime->getDateTime(), $endDateTime->getTimeZone()) : null,
            'isAllDay' => $microsoftEvent->getIsAllDay() ?? false,
            'recurrence' => $this->buildRecurrenceArray($recurrence),
            'recurrenceEventId' => $microsoftEvent->getSeriesMasterId(),
            'location' => $microsoftEvent->getLocation()?->getDisplayName(),
        ], $calendar, $this->account);
    }

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
    protected function formatRecurrenceArray(array $recurrence, Carbon $startDate): PatternedRecurrence
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
        if ($recurrence['interval'] ?? null) {
            $pattern->setInterval($recurrence['interval']);
        }
        $range = new RecurrenceRange;
        /** @phpstan-ignore-next-line For some reason Microsoft doesn't like this in a DateTime object */
        $range->setStartDate($startDate->format('Y-m-d'));
        if ($recurrence['count'] ?? null) {
            $range->setNumberOfOccurrences($recurrence['count']);
            $range->setType(new RecurrenceRangeType(RecurrenceRangeType::NUMBERED));
        } elseif ($recurrence['until'] ?? null) {
            $range->setEndDate($recurrence['until']);
            $range->setType(new RecurrenceRangeType(RecurrenceRangeType::END_DATE));
        } else {
            $range->setType(new RecurrenceRangeType(RecurrenceRangeType::NO_END));
        }

        $patternedRecurrence->setPattern($pattern);
        $patternedRecurrence->setRange($range);

        return $patternedRecurrence;
    }

    protected function buildUrlFromOptions(string $baseUrl, array $options): string
    {
        $query = [];

        if (isset($options['search'])) {
            $query['$search'] = $options['search'];
        }

        $query['$top'] = $options['first'] ?? 25;

        $query['$skip'] = $query['$top'] * ($options['page'] ?? 0);

        $queryFields = [];

        foreach ($query as $field => $value) {
            $queryFields[] = "$field=$value";
        }

        $delimiter = str_contains($baseUrl, '?') ? '&' : '?';

        return "$baseUrl$delimiter".implode('&', $queryFields);
    }

    protected function getCalendarId(?string $calendarId): string
    {
        if (! $calendarId) {
            return '';
        }

        return Str::startsWith($calendarId, $this->account->account_name.'::')
            ? mb_substr($calendarId, mb_strlen($this->account->account_name) + 2)
            : $calendarId;
    }
}
