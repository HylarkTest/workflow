<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\LighthouseHelpers\InteractsWithGraphQLExceptionHandling;

uses(InteractsWithGraphQLExceptionHandling::class);
uses(RefreshDatabase::class);

test('a user can fetch recurring events for a calendar', function () {
    $user = createUser();
    /** @var \App\Models\Calendar $calendar */
    $calendar = $user->firstSpace()->createDefaultCalendar();
    // This also tests DST
    /** @var \App\Models\Event $event */
    $event = $calendar->events()->create([
        'name' => 'Quidditch practice',
        'start_at' => '2023-03-22 13:00:00',
        'end_at' => '2023-03-22 14:00:00',
        'timezone' => 'Europe/London',
        'recurrence' => [
            'frequency' => 'WEEKLY',
            'byDay' => ['WE'],
            'interval' => 1,
            'count' => 2,
        ],
    ]);

    $this->be($user)->graphQL('
    query GetEvents($calendarId: ID!, $startsBefore: DateTime, $endsAfter: DateTime) {
        events(
            calendarId: $calendarId,
            startsBefore: $startsBefore,
            endsAfter: $endsAfter,
            includeRecurringInstances: true
        ) {
            edges {
                node {
                    id
                    name
                    startAt
                    endAt
                }
            }
        }
    }
    ', [
        'calendarId' => $calendar->globalId(),
        'startsBefore' => '2023-03-31 00:00:00',
        'endsAfter' => '2023-03-01 00:00:00',
    ])->assertJsonCount(2, 'data.events.edges')->assertJson([
        'data' => ['events' => ['edges' => [
            ['node' => [
                'id' => $event->globalId().'_20230322T130000Z',
                'name' => 'Quidditch practice',
                'startAt' => '2023-03-22T13:00:00+00:00',
                'endAt' => '2023-03-22T14:00:00+00:00',
            ],
            ],
            ['node' => [
                'id' => $event->globalId().'_20230329T120000Z',
                'name' => 'Quidditch practice',
                'startAt' => '2023-03-29T12:00:00+00:00',
                'endAt' => '2023-03-29T13:00:00+00:00',
            ],
            ]]]],
    ]);
});

test('a user cannot use the wrong query to fetch external events', function () {
    $this->withGraphQLExceptionHandling();
    $user = createUser();
    $user->firstSpace()->createDefaultCalendar();

    $this->be($user)->graphQL('
    {
        events(calendarId: "SW50ZWdyYXRpb25BY2NvdW50OjI0::AQMkADAwATM3ZmYAZS01ZTU1AC1jYTEyLTAwAi0wMAoARgAAA5aKfkuSMjlEg3d8K64TYLwHAH0mCohDhKROo3gHqc_EfkMAAAIBBgAAAH0mCohDhKROo3gHqc_EfkMAAAGvkhgAAAA=") {
            edges {
                node {
                    id
                    name
                    description
                    startAt
                    endAt
                }
            }
        }
    }
    ')->assertGraphQLErrorMessage('No results for the requested node(s).');
});

test('a user can fetch an instance of a recurring event', function () {
    $user = createUser();
    $calendar = createList($user, 'calendar');
    /** @var \App\Models\Event $event */
    $event = $calendar->events()->create([
        'name' => 'Quidditch practice',
        'start_at' => '2022-09-12 12:00:00',
        'end_at' => '2022-09-12 13:00:00',
        'timezone' => 'UTC',
        'recurrence' => [
            'frequency' => 'WEEKLY',
            'byDay' => ['MO', 'WE', 'FR'],
        ],
    ]);

    // Updating a specific instance of Quidditch practice should create a separate instance
    $this->be($user)->assertGraphQL([
        "event(id: \"{$event->globalId()}_20220914T120000Z\", full: false)" => [
            'id' => $event->globalId().'_20220914T120000Z',
        ],
    ]);
});

test('a user cannot create an event with end date before start date', function () {
    $this->withGraphQLExceptionHandling();
    $user = createUser();
    /** @var \App\Models\Calendar $calendar */
    $calendar = $user->firstSpace()->createDefaultCalendar();

    $this->be($user)->graphQL('
    mutation CreateEvent($input: CreateEventInput!) {
        createEvent(input: $input) {
            event { id }
            calendar { id }
        }
    }
    ', [
        'input' => [
            'calendarId' => $calendar->globalId(),
            'name' => 'Hogsmeade visit',
            'startAt' => '2022-07-24 13:00:00',
            'endAt' => '2022-07-24 12:00:00',
            'timezone' => 'UTC',
        ],
    ])->assertGraphQLValidationError('input.endAt', 'The end at date must be a date after or equal to start at date.');
});

test('updating a recurring instance creates multiple events', function () {
    $user = createUser();
    $calendar = $user->firstSpace()->createDefaultCalendar();
    /** @var \App\Models\Event $event */
    $event = $calendar->events()->create([
        'name' => 'Quidditch practice',
        'start_at' => '2022-09-12 12:00:00',
        'end_at' => '2022-09-12 13:00:00',
        'timezone' => 'UTC',
        'recurrence' => [
            'frequency' => 'WEEKLY',
            'byDay' => ['MO', 'WE', 'FR'],
        ],
    ]);

    // Updating a specific instance of Quidditch practice should create a separate instance
    $this->be($user)->graphQL('
    mutation UpdateEvent($input: UpdateEventInput!) {
        updateEvent(input: $input) {
            event { id }
            calendar { id }
        }
    }
    ', [
        'input' => [
            'calendarId' => $calendar->globalId(),
            'id' => $event->globalId().'_20220921T120000Z',
            'name' => 'Extended quidditch practice',
            'startAt' => '2022-09-21 12:00:00',
            'endAt' => '2022-09-21 14:00:00',
            'thisAndFuture' => false,
        ],
    ])->assertSuccessfulGraphQL();

    expect($calendar->events)->toHaveCount(3);

    $this->be($user)->graphQL('
    query GetEvents($calendarId: ID!, $startsBefore: DateTime, $endsAfter: DateTime) {
        events(
            calendarId: $calendarId,
            startsBefore: $startsBefore,
            endsAfter: $endsAfter,
            includeRecurringInstances: true
        ) {
            edges {
                node {
                    id
                    name
                    startAt
                    endAt
                    recurrence {
                        frequency
                        byDay
                        count
                        until
                    }
                }
            }
        }
    }
    ', [
        'calendarId' => $calendar->globalId(),
        'endsAfter' => '2022-09-13',
        'startsBefore' => '2022-09-27',
    ])->assertSuccessful()
        ->assertJsonCount(6, 'data.events.edges')
        ->assertJson(['data' => ['events' => ['edges' => [
            ['node' => ['name' => 'Quidditch practice', 'startAt' => '2022-09-14T12:00:00+00:00']],
            ['node' => ['name' => 'Quidditch practice', 'startAt' => '2022-09-16T12:00:00+00:00']],
            ['node' => ['name' => 'Quidditch practice', 'startAt' => '2022-09-19T12:00:00+00:00']],
            ['node' => ['name' => 'Extended quidditch practice', 'startAt' => '2022-09-21T12:00:00+00:00']],
            ['node' => ['name' => 'Quidditch practice', 'startAt' => '2022-09-23T12:00:00+00:00']],
            ['node' => ['name' => 'Quidditch practice', 'startAt' => '2022-09-26T12:00:00+00:00']],
        ]]]]);
});

test('the recurrence of an event can be updated', function () {
    $user = createUser();
    $calendar = $user->firstSpace()->createDefaultCalendar();
    /** @var \App\Models\Event $event */
    $event = $calendar->events()->create([
        'name' => 'Quidditch practice',
        'start_at' => '2022-09-12 12:00:00',
        'end_at' => '2022-09-12 13:00:00',
        'timezone' => 'UTC',
        'recurrence' => [
            'frequency' => 'DAILY',
            'interval' => 1,
        ],
    ]);

    $this->be($user)->graphQL('
    mutation UpdateEvent($input: UpdateEventInput!) {
        updateEvent(input: $input) {
            event { id }
        }
    }
    ', [
        'input' => [
            'id' => $event->globalId().'_20220914T120000Z',
            'recurrence' => [
                'frequency' => 'DAILY',
                'interval' => 2,
            ],
        ],
    ])->assertSuccessfulGraphQL();

    expect($calendar->events)->toHaveCount(2);

    $this->be($user)->graphQL('
    query GetEvents($calendarId: ID!, $startsBefore: DateTime, $endsAfter: DateTime) {
        events(
            calendarId: $calendarId,
            startsBefore: $startsBefore,
            endsAfter: $endsAfter,
            includeRecurringInstances: true
        ) {
            edges {
                node {
                    id
                    name
                    startAt
                }
            }
        }
    }
    ', [
        'calendarId' => $calendar->globalId(),
        'endsAfter' => '2022-09-12',
        'startsBefore' => '2022-09-17',
    ])->assertSuccessful()
        ->assertJsonCount(4, 'data.events.edges')
        ->assertJson(['data' => ['events' => ['edges' => [
            ['node' => ['name' => 'Quidditch practice', 'startAt' => '2022-09-12T12:00:00+00:00']],
            ['node' => ['name' => 'Quidditch practice', 'startAt' => '2022-09-13T12:00:00+00:00']],
            ['node' => ['name' => 'Quidditch practice', 'startAt' => '2022-09-14T12:00:00+00:00']],
            // Skip a day with the new recurrence
            ['node' => ['name' => 'Quidditch practice', 'startAt' => '2022-09-16T12:00:00+00:00']],
        ]]]]);
});

test('a recurring event and all future can be updated', function () {
    $user = createUser();
    $calendar = $user->firstSpace()->createDefaultCalendar();
    /** @var \App\Models\Event $event */
    $event = $calendar->events()->create([
        'name' => 'Quidditch practice',
        'start_at' => '2022-09-06 12:00:00',
        'end_at' => '2022-09-06 13:00:00',
        'timezone' => 'UTC',
        'recurrence' => [
            'frequency' => 'DAILY',
            'interval' => 2,
            'count' => 5,
        ],
    ]);

    // Updating a specific instance of Quidditch practice should create a separate instance
    $this->be($user)->graphQL('
    mutation UpdateEvent($input: UpdateEventInput!) {
        updateEvent(input: $input) {
            event { id }
            calendar { id }
        }
    }
    ', [
        'input' => [
            'calendarId' => $calendar->globalId(),
            'id' => $event->globalId().'_20220910T120000Z',
            'name' => 'Extended quidditch practice',
            'startAt' => '2022-09-10 12:30:00',
            'endAt' => '2022-09-10 14:00:00',
            'thisAndFuture' => true,
        ],
    ])->assertSuccessfulGraphQL();

    expect($calendar->events)->toHaveCount(2);

    $this->be($user)->graphQL('
    query GetEvents($calendarId: ID!, $startsBefore: DateTime, $endsAfter: DateTime) {
        events(
            calendarId: $calendarId,
            startsBefore: $startsBefore,
            endsAfter: $endsAfter,
            includeRecurringInstances: true
        ) {
            edges {
                node {
                    id
                    name
                    startAt
                    endAt
                }
            }
        }
    }
    ', [
        'calendarId' => $calendar->globalId(),
        'endsAfter' => '2022-09-01',
        'startsBefore' => '2022-09-30',
    ])->assertSuccessful()
        ->assertJsonCount(5, 'data.events.edges')
        ->assertJson(['data' => ['events' => ['edges' => [
            ['node' => ['name' => 'Quidditch practice', 'startAt' => '2022-09-06T12:00:00+00:00']],
            ['node' => ['name' => 'Quidditch practice', 'startAt' => '2022-09-08T12:00:00+00:00']],
            ['node' => ['name' => 'Extended quidditch practice', 'startAt' => '2022-09-10T12:30:00+00:00']],
            ['node' => ['name' => 'Extended quidditch practice', 'startAt' => '2022-09-12T12:30:00+00:00']],
            ['node' => ['name' => 'Extended quidditch practice', 'startAt' => '2022-09-14T12:30:00+00:00']],
        ]]]]);
});

test('updating the first event works as expected', function () {
    $user = createUser();
    $calendar = $user->firstSpace()->createDefaultCalendar();
    /** @var \App\Models\Event $event */
    $event = $calendar->events()->create([
        'name' => 'Quidditch practice',
        'start_at' => '2022-09-06 12:00:00',
        'end_at' => '2022-09-06 13:00:00',
        'timezone' => 'UTC',
        'recurrence' => [
            'frequency' => 'DAILY',
            'interval' => 2,
            'count' => 5,
        ],
    ]);

    // Updating a specific instance of Quidditch practice should create a separate instance
    $this->be($user)->graphQL('
    mutation UpdateEvent($input: UpdateEventInput!) {
        updateEvent(input: $input) {
            event { id }
            calendar { id }
        }
    }
    ', [
        'input' => [
            'calendarId' => $calendar->globalId(),
            'id' => $event->globalId().'_20220906T120000Z',
            'name' => 'Quidditch time',
            'thisAndFuture' => true,
        ],
    ])->assertSuccessfulGraphQL();

    expect($calendar->events)->toHaveCount(1);
    expect($calendar->events->first()->name)->toBe('Quidditch time');

    $this->be($user)->graphQL('
    mutation UpdateEvent($input: UpdateEventInput!) {
        updateEvent(input: $input) {
            event { id }
            calendar { id }
        }
    }
    ', [
        'input' => [
            'calendarId' => $calendar->globalId(),
            'id' => $event->globalId().'_20220906T120000Z',
            'name' => 'First Quidditch practice',
            'recurrence' => [
                'frequency' => 'DAILY',
                'interval' => 2,
                'count' => 5,
            ],
        ],
    ])->assertSuccessfulGraphQL();

    $calendar->refresh();
    expect($calendar->events)->toHaveCount(2);
    expect($calendar->events->first()->name)->toBe('First Quidditch practice');
    expect($calendar->events->last()->name)->toBe('Quidditch time');
});

it('removes recurrence if there is only one iteration', function () {
    $user = createUser();
    $calendar = $user->firstSpace()->createDefaultCalendar();
    /** @var \App\Models\Event $event */
    $event = $calendar->events()->create([
        'name' => 'Quidditch practice',
        'start_at' => '2022-09-06 12:00:00',
        'end_at' => '2022-09-06 13:00:00',
        'timezone' => 'UTC',
        'recurrence' => [
            'frequency' => 'DAILY',
            'interval' => 2,
            'count' => 5,
        ],
    ]);

    // Updating a specific instance of Quidditch practice should create a separate instance
    $this->be($user)->graphQL('
    mutation UpdateEvent($input: UpdateEventInput!) {
        updateEvent(input: $input) {
            event { id }
            calendar { id }
        }
    }
    ', [
        'input' => [
            'calendarId' => $calendar->globalId(),
            'id' => $event->globalId().'_20220908T120000Z',
            'name' => 'Extended quidditch practice',
            'startAt' => '2022-09-08 12:00:00',
            'endAt' => '2022-09-08 14:00:00',
            'thisAndFuture' => false,
        ],
    ])->assertSuccessfulGraphQL();

    expect($calendar->events)->toHaveCount(3);

    expect($calendar->events->first()->recurrence)->toBeNull();
    expect($calendar->events->get(1)->recurrence)->toBeNull();
});

test('deleting the first event deletes all', function () {
    $user = createUser();
    $calendar = $user->firstSpace()->createDefaultCalendar();
    /** @var \App\Models\Event $event */
    $event = $calendar->events()->create([
        'name' => 'Quidditch practice',
        'start_at' => '2022-09-06 11:00:00',
        'end_at' => '2022-09-06 13:00:00',
        'timezone' => 'UTC',
        'recurrence' => [
            'frequency' => 'DAILY',
            'interval' => 2,
        ],
    ]);

    // Updating a specific instance of Quidditch practice should create a separate instance
    $this->be($user)->graphQL('
    mutation DeleteEvent($input: DeleteEventInput!) {
        deleteEvent(input: $input) {
            event { id }
            calendar { id }
        }
    }
    ', [
        'input' => [
            'id' => $event->globalId().'_20220906T110000Z',
            'thisAndFuture' => true,
        ],
    ])->assertSuccessfulGraphQL();

    expect($calendar->events)->toBeEmpty();
});

test('a recurring event can be deleted', function () {
    $user = createUser();
    $calendar = $user->firstSpace()->createDefaultCalendar();
    /** @var \App\Models\Event $event */
    $event = $calendar->events()->create([
        'name' => 'Quidditch practice',
        'start_at' => '2023-03-22 13:00:00',
        'end_at' => '2023-03-22 14:00:00',
        'timezone' => 'Europe/London',
        'recurrence' => [
            'frequency' => 'WEEKLY',
            'byDay' => ['WE'],
            'interval' => 1,
            'count' => 5,
        ],
    ]);

    // Updating a specific instance of Quidditch practice should create a separate instance
    $this->be($user)->graphQL('
    mutation DeleteEvent($input: DeleteEventInput!) {
        deleteEvent(input: $input) {
            event { id }
            calendar { id }
        }
    }
    ', [
        'input' => [
            'id' => $event->globalId().'_20230329T120000Z', // Using an hour before to test DST
            'thisAndFuture' => false,
        ],
    ])->assertSuccessfulGraphQL();

    expect($calendar->events)->toHaveCount(2);

    $this->be($user)->graphQL('
    query GetEvents($calendarId: ID!, $startsBefore: DateTime, $endsAfter: DateTime) {
        events(
            calendarId: $calendarId,
            startsBefore: $startsBefore,
            endsAfter: $endsAfter,
            includeRecurringInstances: true
        ) {
            edges {
                node {
                    id
                    name
                    startAt
                    endAt
                }
            }
        }
    }
    ', [
        'calendarId' => $calendar->globalId(),
        'endsAfter' => '2023-03-01',
        'startsBefore' => '2023-04-30',
    ])->assertSuccessful()
        ->assertJsonCount(4, 'data.events.edges')
        ->assertJson(['data' => ['events' => ['edges' => [
            ['node' => ['name' => 'Quidditch practice', 'startAt' => '2023-03-22T13:00:00+00:00']],
            ['node' => ['name' => 'Quidditch practice', 'startAt' => '2023-04-05T12:00:00+00:00']],
            ['node' => ['name' => 'Quidditch practice', 'startAt' => '2023-04-12T12:00:00+00:00']],
            ['node' => ['name' => 'Quidditch practice', 'startAt' => '2023-04-19T12:00:00+00:00']],
        ]]]]);
});

test('a recurring event and all future can be deleted', function () {
    $user = createUser();
    $calendar = $user->firstSpace()->createDefaultCalendar();
    /** @var \App\Models\Event $event */
    $event = $calendar->events()->create([
        'name' => 'Quidditch practice',
        'start_at' => '2022-09-06 12:00:00',
        'end_at' => '2022-09-06 13:00:00',
        'isAllDay' => true,
        'timezone' => 'UTC',
        'recurrence' => [
            'frequency' => 'DAILY',
            'interval' => 2,
            'count' => 5,
        ],
    ]);

    // Updating a specific instance of Quidditch practice should create a separate instance
    $this->be($user)->graphQL('
    mutation DeleteEvent($input: DeleteEventInput!) {
        deleteEvent(input: $input) {
            event { id }
            calendar { id }
        }
    }
    ', [
        'input' => [
            'id' => $event->globalId().'_20220910T120000Z',
            'thisAndFuture' => true,
        ],
    ])->assertSuccessfulGraphQL();

    expect($calendar->events)->toHaveCount(1);

    $this->be($user)->graphQL('
    query GetEvents($calendarId: ID!, $startsBefore: DateTime, $endsAfter: DateTime) {
        events(
            calendarId: $calendarId,
            startsBefore: $startsBefore,
            endsAfter: $endsAfter,
            includeRecurringInstances: true
        ) {
            edges {
                node {
                    id
                    name
                    startAt
                    endAt
                    recurrence {
                        frequency
                        byDay
                        count
                        until
                    }
                }
            }
        }
    }
    ', [
        'calendarId' => $calendar->globalId(),
        'endsAfter' => '2022-09-01',
        'startsBefore' => '2022-09-30',
    ])->assertSuccessful()
        ->assertJsonCount(2, 'data.events.edges')
        ->assertJson(['data' => ['events' => ['edges' => [
            ['node' => ['name' => 'Quidditch practice', 'startAt' => '2022-09-06T12:00:00+00:00']],
            ['node' => ['name' => 'Quidditch practice', 'startAt' => '2022-09-08T12:00:00+00:00']],
        ]]]]);
});

test('recurring events only occur once in the response', function () {
    $user = createUser();
    /** @var \App\Models\Calendar $calendar */
    $calendar = $user->firstSpace()->createDefaultCalendar();
    /** @var \App\Models\Event $event */
    $event = $calendar->events()->create([
        'name' => 'Charms class',
        'start_at' => now(),
        'end_at' => now()->addHour(),
        'timezone' => 'utc',
        'recurrence' => [
            'frequency' => 'daily',
            'interval' => 2,
            'count' => 3,
        ],
    ]);

    $this->be($user)->graphQL("
    {
        events(calendarId: \"{$calendar->globalId()}\") {
            edges {
                node {
                    id
                    recurrence {
                        frequency
                        interval
                        count
                    }
                }
            }
        }
    }
    ")->assertJsonCount(1, 'data.events.edges')->assertJson([
        'data' => ['events' => ['edges' => [[
            'node' => [
                'id' => $event->globalId(),
                'recurrence' => [
                    'frequency' => 'DAILY',
                    'interval' => 2,
                    'count' => 3,
                ],
            ],
        ]]]],
    ]);
});

test('recurring events can be fetched as separate events', function () {
    $user = createUser();
    /** @var \App\Models\Calendar $calendar */
    $calendar = $user->firstSpace()->createDefaultCalendar();
    /** @var \App\Models\Event $event */
    $event = $calendar->events()->create([
        'name' => 'Charms class',
        'start_at' => '2022-08-09 12:00:00',
        'end_at' => '2022-08-09 13:00:00',
        'timezone' => 'utc',
        'recurrence' => [
            'frequency' => 'daily',
            'interval' => 2,
            'count' => 3,
        ],
    ]);

    $min = '2022-08-08 00:00:00';
    $max = '2022-08-16 00:00:00';

    $this->be($user)->graphQL("
    {
        events(
            calendarId: \"{$calendar->globalId()}\",
            includeRecurringInstances: true,
            endsAfter: \"$min\"
            startsBefore: \"$max\",
        ) {
            edges {
                node {
                    id
                    recurrence {
                        frequency
                        interval
                        count
                    }
                }
            }
        }
    }
    ")->assertJsonCount(3, 'data.events.edges')->assertJson([
        'data' => ['events' => ['edges' => [
            ['node' => [
                'id' => $event->globalId().'_20220809T120000Z',
                'recurrence' => [
                    'frequency' => 'DAILY',
                    'interval' => 2,
                    'count' => 3,
                ],
            ]],
            ['node' => [
                'id' => $event->globalId().'_20220811T120000Z',
                'recurrence' => [
                    'frequency' => 'DAILY',
                    'interval' => 2,
                    'count' => 3,
                ],
            ]],
            ['node' => [
                'id' => $event->globalId().'_20220813T120000Z',
                'recurrence' => [
                    'frequency' => 'DAILY',
                    'interval' => 2,
                    'count' => 3,
                ],
            ]],
        ]]],
    ]);
});

test('recurring events beginning outside the current range are fetched', function () {
    $user = createUser();
    /** @var \App\Models\Calendar $calendar */
    $calendar = $user->firstSpace()->createDefaultCalendar();
    /** @var \App\Models\Event $event */
    $event = $calendar->events()->create([
        'name' => 'Charms class',
        'start_at' => '2022-08-09 12:00:00',
        'end_at' => '2022-08-09 13:00:00',
        'timezone' => 'utc',
        'recurrence' => [
            'frequency' => 'monthly',
            'count' => 3,
        ],
    ]);

    $min = '2022-09-01 00:00:00';
    $max = '2022-09-30 00:00:00';

    $this->be($user)->graphQL("
    {
        events(
            calendarId: \"{$calendar->globalId()}\",
            includeRecurringInstances: true,
            endsAfter: \"$min\"
            startsBefore: \"$max\",
        ) {
            edges {
                node {
                    id
                    recurrence {
                        frequency
                        count
                    }
                }
            }
        }
    }
    ")->assertJsonCount(1, 'data.events.edges')->assertJson([
        'data' => ['events' => ['edges' => [
            ['node' => [
                'id' => $event->globalId().'_20220909T120000Z',
                'recurrence' => [
                    'frequency' => 'MONTHLY',
                    'count' => 3,
                ],
            ]],
        ]]],
    ]);
});

test('switching the calendar of an instance changes the whole event', function () {
    $user = createUser();
    $calendar = $user->firstSpace()->createDefaultCalendar();
    $otherCalendar = $user->firstSpace()->calendars()->create(['name' => 'Quiddith']);
    /** @var \App\Models\Event $event */
    $event = $calendar->events()->create([
        'name' => 'Quidditch practice',
        'start_at' => '2022-09-06 12:00:00',
        'end_at' => '2022-09-06 13:00:00',
        'timezone' => 'UTC',
        'recurrence' => [
            'frequency' => 'DAILY',
            'interval' => 2,
            'count' => 5,
        ],
    ]);

    // Updating a specific instance of Quidditch practice should create a separate instance
    $this->be($user)->graphQL('
    mutation UpdateEvent($input: UpdateEventInput!) {
        updateEvent(input: $input) {
            event { id }
            calendar { id }
        }
    }
    ', [
        'input' => [
            'calendarId' => $otherCalendar->globalId(),
            'id' => $event->globalId().'_20220906T120000Z',
        ],
    ])->assertSuccessfulGraphQL();

    expect($calendar->events)->toBeEmpty()
        ->and($otherCalendar->events)->toHaveCount(1);
});
