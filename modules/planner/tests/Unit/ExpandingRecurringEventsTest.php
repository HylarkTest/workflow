<?php

declare(strict_types=1);

namespace Tests\Planner\Unit;

use Planner\Models\Event;
use Tests\Planner\TestCase;
use Illuminate\Support\Carbon;

class ExpandingRecurringEventsTest extends TestCase
{
    /**
     * A collection of events can be expanded into individual events
     *
     * @test
     */
    public function a_collection_of_events_can_be_expanded_into_individual_events(): void
    {
        $events = (new Event)->newCollection([
            (new Event)->forceFill([
                'id' => 1,
                'name' => 'Non-recurring event',
                'start_at' => '2022-09-01T00:00:00+00:00',
                'end_at' => '2022-09-02T00:00:00+00:00',
                'timezone' => 'UTC',
            ]),
            (new Event)->forceFill([
                'id' => 2,
                'name' => 'Recurring event',
                'start_at' => '2022-09-03T00:00:00+00:00',
                'end_at' => '2022-09-03T01:00:00+00:00',
                'timezone' => 'UTC',
                'recurrence' => [
                    'frequency' => 'DAILY',
                    'interval' => 2,
                ],
            ]),
            (new Event)->forceFill([
                'id' => 3,
                'name' => 'Middle event',
                'start_at' => '2022-09-06T00:00:00+00:00',
                'end_at' => '2022-09-06T01:00:00+00:00',
                'timezone' => 'UTC',
            ]),
        ]);

        $expandedEvents = $events->expandRecurringEvents(Carbon::parse('2022-09-12'));

        static::assertCount(7, $expandedEvents);
        static::assertSame(3, $expandedEvents->get(3)->id);
    }
}
