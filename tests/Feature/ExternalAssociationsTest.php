<?php

declare(strict_types=1);

use Mockery\MockInterface;
use AccountIntegrations\Core\Todos\Todo;
use AccountIntegrations\Core\Emails\Email;
use AccountIntegrations\Core\Calendar\Event;
use AccountIntegrations\Core\Emails\Mailbox;
use AccountIntegrations\Core\Todos\TodoList;
use AccountIntegrations\Core\Calendar\Calendar;
use AccountIntegrations\Models\IntegrationAccount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use AccountIntegrations\Core\Todos\Repositories\MicrosoftTodoRepository;
use AccountIntegrations\Core\Calendar\Repositories\MicrosoftCalendarRepository;
use AccountIntegrations\Core\Emails\Repositories\MicrosoftGraphEmailRepository;

uses(RefreshDatabase::class);

test('an external event can be fetched with associations', function () {
    $user = createUser();

    $mapping = createMapping($user);
    $item = createItem($mapping);

    $integration = createIntegrationAccount($user);

    bindMockCalendarRepository($integration);

    $this->be($user)->graphQL('
    mutation AssociateItem($input: AssociateExternalEventInput!) {
        associateExternalEvent(input: $input) {
            code
            event {
                id
                name
                associations {
                    id
                }
            }
        }
    }
    ', ['input' => [
        'nodeId' => $item->global_id,
        'sourceId' => $integration->global_id,
        'calendarId' => base64_encode('def456'),
        'id' => 'abc123',
    ]])->assertSuccessfulGraphQL()->assertJson(['data' => ['associateExternalEvent' => [
        'event' => [
            'id' => 'abc123',
            'name' => 'My Event',
            'associations' => [[
                'id' => $item->global_id,
            ]],
        ],
    ]]]);
});

test('an external event can be dissociated', function () {
    $user = createUser();

    $mapping = createMapping($user);
    $item = createItem($mapping, ['SYSTEM_NAME' => 'Larry']);

    $integration = createIntegrationAccount($user);
    bindMockCalendarRepository($integration);

    $item->externalEventables()->create([
        'calendar_id' => 'def456',
        'event_id' => 'abc123',
        'integration_account_id' => $integration->id,
    ]);

    $this->be($user)->graphQL('
    mutation DissociateItem($input: DissociateExternalEventInput!) {
        dissociateExternalEvent(input: $input) {
            code
            event {
                id
                name
                associations {
                    id
                }
            }
        }
    }
    ', ['input' => [
        'nodeId' => $item->global_id,
        'sourceId' => $integration->global_id,
        'calendarId' => base64_encode('def456'),
        'id' => 'abc123',
    ]])->assertSuccessfulGraphQL()->assertJson(['data' => ['dissociateExternalEvent' => [
        'event' => [
            'id' => 'abc123',
            'name' => 'My Event',
            'associations' => [],
        ],
    ]]]);
});

test('external events can be fetched by items', function () {
    $user = createUser();

    $mapping = createMapping($user);
    $item = createItem($mapping);

    $integration = createIntegrationAccount($user);
    bindMockCalendarRepository($integration);

    $item->externalEventables()->create([
        'calendar_id' => 'def456',
        'event_id' => 'abc123',
        'integration_account_id' => $integration->id,
    ]);

    $this->be($user)->graphQL("
    query ExternalEvents {
        externalEvents(forNode: \"$item->global_id\") {
            data {
                id
                name
                calendar {
                    id
                    name
                }
            }
        }
    }
    ")->assertSuccessfulGraphQL()->assertJson(['data' => ['externalEvents' => [
        'data' => [[
            'id' => 'abc123',
            'name' => 'My Event',
            'calendar' => [
                'id' => base64_encode('def456'),
                'name' => 'My Calendar',
            ],
        ]],
    ]]]);
});

test('an external todo can be fetched with associations', function () {
    $user = createUser();

    $mapping = createMapping($user);
    $item = createItem($mapping);

    $integration = createIntegrationAccount($user);
    bindMockTodoRepository($integration);

    $this->be($user)->graphQL('
    mutation AssociateItem($input: AssociateExternalTodoInput!) {
        associateExternalTodo(input: $input) {
            code
            todo {
                id
                name
                associations {
                    id
                }
            }
        }
    }
    ', ['input' => [
        'nodeId' => $item->global_id,
        'sourceId' => $integration->global_id,
        'todoListId' => base64_encode('def456'),
        'id' => 'abc123',
    ]])->assertSuccessfulGraphQL()->assertJson(['data' => ['associateExternalTodo' => [
        'todo' => [
            'id' => 'abc123',
            'name' => 'My Todo',
            'associations' => [[
                'id' => $item->global_id,
            ]],
        ],
    ]]]);
});

test('an external todo can be dissociated', function () {
    $user = createUser();

    $mapping = createMapping($user);
    $item = createItem($mapping);

    $integration = createIntegrationAccount($user);
    bindMockTodoRepository($integration);

    $item->externalTodoables()->create([
        'todo_list_id' => 'def456',
        'todo_id' => 'abc123',
        'integration_account_id' => $integration->id,
    ]);

    $this->be($user)->graphQL('
    mutation DissociateItem($input: DissociateExternalTodoInput!) {
        dissociateExternalTodo(input: $input) {
            code
            todo {
                id
                name
                associations {
                    id
                }
            }
        }
    }
    ', ['input' => [
        'nodeId' => $item->global_id,
        'sourceId' => $integration->global_id,
        'todoListId' => base64_encode('def456'),
        'id' => 'abc123',
    ]])->assertSuccessfulGraphQL()->assertJson(['data' => ['dissociateExternalTodo' => [
        'todo' => [
            'id' => 'abc123',
            'name' => 'My Todo',
            'associations' => [],
        ],
    ]]]);
});

test('external todos can be fetched by items', function () {
    $user = createUser();

    $mapping = createMapping($user);
    $item = createItem($mapping);

    $integration = createIntegrationAccount($user);
    bindMockTodoRepository($integration);

    $item->externalTodoables()->create([
        'todo_list_id' => 'def456',
        'todo_id' => 'abc123',
        'integration_account_id' => $integration->id,
    ]);

    $this->be($user)->graphQL("
    query ExternalTodos {
        externalTodos(forNode: \"$item->global_id\") {
            data {
                id
                name
                list {
                    id
                    name
                }
            }
        }
    }
    ")->assertSuccessfulGraphQL()->assertJson(['data' => ['externalTodos' => [
        'data' => [[
            'id' => 'abc123',
            'name' => 'My Todo',
            'list' => [
                'id' => base64_encode('def456'),
                'name' => 'My Todo list',
            ],
        ]],
    ]]]);
});

test('an external email can be fetched with associations', function () {
    $user = createUser();

    $mapping = createMapping($user);
    $item = createItem($mapping, ['name' => 'Name']);

    $integration = createIntegrationAccount($user);
    bindMockEmailRepository($integration);

    $this->be($user)->assertGraphQLMutation(
        ['associateEmail(input: $input)' => [
            'email' => [
                'id' => 'abc123',
                'subject' => 'My Email',
                'associations' => [[
                    'id' => $item->global_id,
                ]],
            ],
        ]],
        ['input: AssociateEmailInput!' => [
            'nodeId' => $item->global_id,
            'sourceId' => $integration->global_id,
            'mailboxId' => base64_encode('def456'),
            'id' => 'abc123',
        ]]);
});

test('if an item is deleted it does not appear in associations', function () {
    $user = createUser();

    $mapping = createMapping($user);
    $item1 = createItem($mapping);
    $item2 = createItem($mapping);

    $integration = createIntegrationAccount($user);
    bindMockEmailRepository($integration);

    $item1->emailables()->create([
        'mailbox_id' => 'def456',
        'email_id' => 'abc123',
        'integration_account_id' => $integration->id,
        'email_created_at' => now(),
    ]);
    $item2->emailables()->create([
        'mailbox_id' => 'def456',
        'email_id' => 'abc123',
        'integration_account_id' => $integration->id,
        'email_created_at' => now(),
    ]);

    $item1->delete();

    $mailboxId = base64_encode('def456');
    $this->be($user)->assertGraphQL([
        "emails(sourceId: \"$integration->global_id\", mailboxId: \"$mailboxId\")" => ['edges' => [
            ['node' => [
                'id' => 'abc123',
                'subject' => 'My Email',
                'associations' => [[
                    'id' => $item2->global_id,
                ]],
            ]],
        ]],
    ]);
});

test('a external emails can be fetched with address associations', function () {
    $user = createUser();

    $mapping = createMapping($user);
    $item = createItem($mapping);

    $integration = createIntegrationAccount($user);
    bindMockEmailRepository($integration);

    $this->be($user)->graphQL('
    mutation AssociateItem($input: AssociateEmailAddressInput!) {
        associateEmailAddress(input: $input) {
            code
        }
    }
    ', ['input' => [
        'nodeId' => $item->global_id,
        'sourceId' => $integration->global_id,
        'mailboxId' => base64_encode('def456'),
        'address' => 'test@example.com',
    ]])->assertSuccessfulGraphQL();

    expect($item->emailAddressables()->exists())->toBeTrue();
    expect($item->emailAddressables()->first()->address)->toBe('test@example.com');
});

test('an external email can be dissociated', function () {
    $user = createUser();

    $mapping = createMapping($user);
    $item = createItem($mapping);

    $integration = createIntegrationAccount($user);
    bindMockEmailRepository($integration);

    $item->emailables()->create([
        'mailbox_id' => 'def456',
        'email_id' => 'abc123',
        'integration_account_id' => $integration->id,
        'email_created_at' => now(),
    ]);

    $this->be($user)->graphQL('
    mutation DissociateItem($input: DissociateEmailInput!) {
        dissociateEmail(input: $input) {
            code
            email {
                id
                subject
                associations {
                    id
                }
            }
        }
    }
    ', ['input' => [
        'nodeId' => $item->global_id,
        'sourceId' => $integration->global_id,
        'mailboxId' => base64_encode('def456'),
        'id' => 'abc123',
    ]])->assertSuccessfulGraphQL()->assertJson(['data' => ['dissociateEmail' => [
        'email' => [
            'id' => 'abc123',
            'subject' => 'My Email',
            'associations' => [],
        ],
    ]]]);
});

test('external emails can be fetched by items', function () {
    $user = createUser();

    $mapping = createMapping($user);
    $item = createItem($mapping);

    $integration = createIntegrationAccount($user);
    bindMockEmailRepository($integration);

    $item->emailables()->create([
        'mailbox_id' => 'def456',
        'email_id' => 'abc123',
        'integration_account_id' => $integration->id,
        'email_created_at' => now(),
    ]);

    $mailboxId = base64_encode('def456');
    $this->be($user)->graphQL("
    query ExternalEmails {
        emails(sourceId: \"$integration->global_id\", mailboxId: \"$mailboxId\", forNode: \"$item->global_id\") {
            edges { node {
                id
                subject
                mailbox {
                    id
                    name
                }
            } }
        }
    }
    ")->assertSuccessfulGraphQL()->assertJson(['data' => ['emails' => [
        'edges' => [['node' => [
            'id' => 'abc123',
            'subject' => 'My Email',
            'mailbox' => [
                'id' => base64_encode('def456'),
                'name' => 'My Mailbox',
            ],
        ]]],
    ]]]);
});

test('external emails can be fetched by address', function () {
    $user = createUser();

    $mapping = createMapping($user);
    $item = createItem($mapping);

    $integration = createIntegrationAccount($user);
    bindMockEmailRepository($integration);

    $item->emailAddressables()->create([
        'mailbox_id' => 'def456',
        'address' => 'test@example.com',
        'integration_account_id' => $integration->id,
    ]);
    $mailboxId = base64_encode('def456');

    $this->be($user)->graphQL("
    query ExternalEmails {
        emails(sourceId: \"$integration->global_id\", mailboxId: \"$mailboxId\", forNode: \"$item->global_id\") {
            edges { node {
                id
                subject
                mailbox {
                    id
                    name
                }
            } }
        }
    }
    ")->assertSuccessfulGraphQL()->assertJson(['data' => ['emails' => [
        'edges' => [['node' => [
            'id' => 'abc123',
            'subject' => 'My Email',
            'mailbox' => [
                'id' => base64_encode('def456'),
                'name' => 'My Mailbox',
            ],
        ]]],
    ]]]);
});

test('a list of nodes associated to email addresses can be fetched', function () {
    $user = createUser();

    $mapping = createMapping($user);
    $item = createItem($mapping);

    $integration = createIntegrationAccount($user);

    $item->emailAddressables()->create([
        'address' => 'test@example.com',
        'integration_account_id' => $integration->id,
    ]);

    $this->be($user)->graphQL('
    query EmailAddressAssociations {
        emailAddressAssociations {
            edges {
                node {
                    address
                    association {
                        id
                    }
                }
            }
        }
    }
    ')->assertSuccessfulGraphQL()->assertJson(['data' => ['emailAddressAssociations' => [
        'edges' => [['node' => [
            'address' => 'test@example.com',
            'association' => [
                'id' => $item->global_id,
            ],
        ]]],
    ]]]);
});

test('direct and address associations can be fetched and merged with pagination', function () {
    $user = createUser();

    $mapping = createMapping($user);
    $item = createItem($mapping);

    $integration = createIntegrationAccount($user);

    $item->emailAddressables()->create([
        'address' => 'test@example.com',
        'integration_account_id' => $integration->id,
    ]);

    $item->emailAddressables()->create([
        'address' => 'example@test.com',
        'integration_account_id' => $integration->id,
    ]);

    $now = now();

    $item->emailables()->createMany([
        [
            'email_id' => '1',
            'mailbox_id' => 'def456',
            'integration_account_id' => $integration->id,
            'email_created_at' => (clone $now)->subSeconds(30),
        ],
        [
            'email_id' => 'b',
            'mailbox_id' => 'def456',
            'integration_account_id' => $integration->id,
            'email_created_at' => (clone $now)->subMinute(),
        ],
        [
            'email_id' => 'g',
            'mailbox_id' => 'def456',
            'integration_account_id' => $integration->id,
            'email_created_at' => (clone $now)->subMinutes(6),
        ],
    ]);

    app()->bind(MicrosoftGraphEmailRepository::class, fn () => \Mockery::mock(MicrosoftGraphEmailRepository::class, function (MockInterface $mock) use ($integration, $now) {
        $mailbox = new Mailbox([
            'id' => 'def456',
            'name' => 'My Mailbox',
        ], $integration);
        $mock->shouldReceive('getEmailsSummary')
            ->withArgs(['def456', [
                'search' => null,
                'addresses' => [
                    'test@example.com',
                    'example@test.com',
                ],
                'ids' => [
                    '1', 'b', 'g',
                ],
                'first' => 4,
                'offset' => 0,
            ]])
            ->andReturn(collect([
                new Email(['id' => 'a', 'subject' => 'Email 1', 'to' => ['test@example.com'], 'createdAt' => $now], $mailbox, $integration),
                new Email(['id' => '1', 'subject' => 'Email A', 'to' => ['someone@mail.com'], 'createdAt' => (clone $now->subSeconds(30))], $mailbox, $integration),
                new Email(['id' => 'b', 'subject' => 'Email 2', 'to' => ['example@test.com'], 'createdAt' => (clone $now)->subMinutes(1)], $mailbox, $integration),
                new Email(['id' => 'c', 'subject' => 'Email 3', 'to' => ['test@example.com'], 'createdAt' => (clone $now)->subMinutes(2)], $mailbox, $integration),
                new Email(['id' => 'd', 'subject' => 'Email 4', 'to' => ['example@test.com'], 'createdAt' => (clone $now)->subMinutes(3)], $mailbox, $integration),
                new Email(['id' => 'e', 'subject' => 'Email 5', 'to' => ['test@example.com'], 'createdAt' => (clone $now)->subMinutes(4)], $mailbox, $integration),
                new Email(['id' => 'f', 'subject' => 'Email 6', 'to' => ['example@test.com'], 'createdAt' => (clone $now)->subMinutes(5)], $mailbox, $integration),
                new Email(['id' => 'g', 'subject' => 'Email 7', 'to' => ['someone@mail.com'], 'createdAt' => (clone $now)->subMinutes(6)], $mailbox, $integration),
            ]));
        $mock->shouldReceive('getEmailsSummary')
            ->withArgs(['def456', [
                'first' => 7,
                'offset' => 3,
                'search' => null,
                'addresses' => [
                    'test@example.com',
                    'example@test.com',
                ],
                'ids' => [
                    '1', 'b', 'g',
                ],
            ]])
            ->andReturn(collect([
                new Email(['id' => 'c', 'subject' => 'Email 3', 'to' => ['test@example.com'], 'createdAt' => (clone $now)->subMinutes(2)], $mailbox, $integration),
                new Email(['id' => 'd', 'subject' => 'Email 4', 'to' => ['example@test.com'], 'createdAt' => (clone $now)->subMinutes(3)], $mailbox, $integration),
                new Email(['id' => 'e', 'subject' => 'Email 5', 'to' => ['test@example.com'], 'createdAt' => (clone $now)->subMinutes(4)], $mailbox, $integration),
                new Email(['id' => 'f', 'subject' => 'Email 6', 'to' => ['example@test.com'], 'createdAt' => (clone $now)->subMinutes(5)], $mailbox, $integration),
                new Email(['id' => 'g', 'subject' => 'Email 7', 'to' => ['someone@mail.com'], 'createdAt' => (clone $now)->subMinutes(6)], $mailbox, $integration),
            ]));
        $mock->shouldReceive('cacheMailboxes');
    }));

    $query = '
    query Emails($first: Int! $forNode: ID!, $after: String, $sourceId: ID!, $mailboxId: ID!) {
        emails(sourceId: $sourceId, mailboxId: $mailboxId, first: $first, forNode: $forNode, after: $after) {
            edges {
                node {
                    id
                }
            }
            pageInfo {
                endCursor
                hasNextPage
            }
        }
    }
    ';

    $mailboxId = base64_encode('def456');
    $response = $this->be($user)->graphQL($query, ['first' => 3, 'forNode' => $item->global_id, 'sourceId' => $integration->global_id, 'mailboxId' => $mailboxId])
        ->assertSuccessfulGraphQL()
        ->assertJsonCount(3, 'data.emails.edges')
        ->assertJson(['data' => ['emails' => [
            'edges' => [
                ['node' => ['id' => 'a']],
                ['node' => ['id' => '1']],
                ['node' => ['id' => 'b']],
            ],
            'pageInfo' => [
                'hasNextPage' => true,
            ],
        ]]])->json();

    $nextPage = $response['data']['emails']['pageInfo']['endCursor'];

    $this->be($user)->graphQL($query, ['first' => 6, 'forNode' => $item->global_id, 'after' => $nextPage, 'sourceId' => $integration->global_id, 'mailboxId' => $mailboxId])
        ->assertSuccessfulGraphQL()
        ->assertJsonCount(5, 'data.emails.edges')
        ->assertJson(['data' => ['emails' => [
            'edges' => [
                ['node' => ['id' => 'c']],
                ['node' => ['id' => 'd']],
                ['node' => ['id' => 'e']],
                ['node' => ['id' => 'f']],
                ['node' => ['id' => 'g']],
            ],
            'pageInfo' => [
                'hasNextPage' => false,
            ],
        ]]])->json();
});

// Helpers

function bindMockCalendarRepository(IntegrationAccount $integration): void
{
    app()->bind(MicrosoftCalendarRepository::class, fn () => \Mockery::mock(MicrosoftCalendarRepository::class, function (MockInterface $mock) use ($integration) {
        $mock->shouldReceive('getEvent')
            ->withArgs(['def456', 'abc123'])
            ->andReturn(new Event([
                'id' => 'abc123',
                'name' => 'My Event',
                'updatedAt' => now(),
            ], new Calendar([
                'id' => 'def456',
                'name' => 'My Calendar',
                'updatedAt' => now(),
            ], $integration), $integration));
    }));
}

function bindMockTodoRepository(IntegrationAccount $integration): void
{
    app()->bind(MicrosoftTodoRepository::class, fn () => \Mockery::mock(MicrosoftTodoRepository::class, function (MockInterface $mock) use ($integration) {
        $mock->shouldReceive('getTodo')
            ->withArgs(['def456', 'abc123'])
            ->andReturn(new Todo([
                'id' => 'abc123',
                'name' => 'My Todo',
                'updatedAt' => now(),
            ], new TodoList([
                'id' => 'def456',
                'name' => 'My Todo list',
                'updatedAt' => now(),
            ], $integration), $integration));
    }));
}

function bindMockEmailRepository(IntegrationAccount $integration): void
{
    app()->bind(MicrosoftGraphEmailRepository::class, fn () => \Mockery::mock(MicrosoftGraphEmailRepository::class, function (MockInterface $mock) use ($integration) {
        $mock->shouldReceive('getEmail')
            ->withArgs(['abc123', 'def456'])
            ->andReturn(new Email([
                'id' => 'abc123',
                'internetMessageId' => 'abc123',
                'subject' => 'My Email',
                'to' => ['you@mail.com'],
                'createdAt' => now(),
            ], new Mailbox([
                'id' => 'def456',
                'name' => 'My Mailbox',
            ], $integration), $integration));

        $result = collect([new Email([
            'id' => 'abc123',
            'subject' => 'My Email',
            'to' => ['test@example.com'],
            'createdAt' => now(),
        ], new Mailbox([
            'id' => 'def456',
            'name' => 'My Mailbox',
        ], $integration), $integration)]);

        $mock->shouldReceive('getEmailsSummary')
            ->withArgs(['def456', ['search' => null, 'addresses' => ['test@example.com'], 'ids' => [], 'first' => 26, 'offset' => 0]])
            ->andReturn($result);
        $mock->shouldReceive('getEmailsSummary')
            ->withArgs(['def456', ['search' => null, 'first' => 26, 'offset' => 0]])
            ->andReturn($result);
        $mock->shouldReceive('getEmailsSummary')
            ->withArgs(['def456', ['search' => null, 'first' => 26, 'offset' => 0, 'addresses' => [], 'ids' => ['abc123']]])
            ->andReturn($result);

        $mock->shouldReceive('getMailbox')
            ->withArgs(['def456'])
            ->andReturn(new Mailbox([
                'id' => 'def456',
                'name' => 'My Mailbox',
            ], $integration));
        $mock->shouldReceive('cacheMailboxes');
    }));
}
