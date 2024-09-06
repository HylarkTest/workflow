<?php

declare(strict_types=1);

namespace Tests\AccountIntegrations\Feature;

use Mockery\MockInterface;
use GuzzleHttp\Promise\Promise;
use Tests\AccountIntegrations\TestCase;
use GuzzleHttp\Promise\PromiseInterface;
use AccountIntegrations\Core\Todos\TodoList;
use AccountIntegrations\Models\IntegrationAccount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use AccountIntegrations\Core\MicrosoftGraphGateway;
use Microsoft\Graph\Model\TodoTask as MicrosoftTodo;
use Microsoft\Graph\Model\TodoTaskList as MicrosoftTodoList;
use AccountIntegrations\Core\Todos\Repositories\MicrosoftTodoRepository;

class MicrosoftTodosTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A user can fetch their todo lists
     *
     * @test
     */
    public function a_user_can_fetch_their_todo_lists(): void
    {
        $this->app->bind(MicrosoftGraphGateway::class, fn () => \Mockery::mock(MicrosoftGraphGateway::class, function (MockInterface $mock) {
            $mock->shouldReceive('getCollection')
                ->withArgs([
                    '/me/todo/lists',
                    MicrosoftTodoList::class,
                    '',
                    MicrosoftTodoList::class,
                ])
                ->andReturn([
                    new MicrosoftTodoList([
                        'id' => '1234',
                        'displayName' => 'General tasks',
                        'isOwner' => true,
                        'isShared' => false,
                        'wellKnownListName' => null,
                    ]),
                ]);
        }));

        $account = new IntegrationAccount(['account_name' => 'test@mail.com']);
        $repository = new MicrosoftTodoRepository($account);

        $lists = $repository->getTodoLists();

        static::assertCount(1, $lists);
        /** @var \AccountIntegrations\Core\Todos\TodoList $list */
        $list = $lists[0];
        static::assertSame($account, $list->account);
        static::assertSame('test@mail.com::1234', $list->id);
        static::assertSame('General tasks', $list->name);
        static::assertFalse($list->isDefault);
        static::assertTrue($list->isOwner);
        static::assertFalse($list->isShared);
    }

    /**
     * A user can create a list
     *
     * @test
     */
    public function a_user_can_create_a_list(): void
    {
        $this->app->bind(MicrosoftGraphGateway::class, fn () => \Mockery::mock(MicrosoftGraphGateway::class, function (MockInterface $mock) {
            $mock->shouldReceive('createItem')
                ->withSomeOfArgs('/me/todo/lists')
                ->andReturn(new MicrosoftTodoList([
                    'id' => '1234',
                    'displayName' => 'General tasks',
                    'isOwner' => true,
                    'isShared' => false,
                    'wellKnownListName' => null,
                ]));
        }));

        $account = new IntegrationAccount(['account_name' => 'test@mail.com']);
        $repository = new MicrosoftTodoRepository($account);

        $list = $repository->createTodoList(new TodoList([
            'name' => 'General tasks',
        ], $account));

        static::assertSame($account, $list->account);
        static::assertSame('test@mail.com::1234', $list->id);
        static::assertSame('General tasks', $list->name);
        static::assertFalse($list->isDefault);
        static::assertTrue($list->isOwner);
        static::assertFalse($list->isShared);
    }

    /**
     * A user can update a list
     *
     * @test
     */
    public function a_user_can_update_a_list(): void
    {
        $this->app->bind(MicrosoftGraphGateway::class, fn () => \Mockery::mock(MicrosoftGraphGateway::class, function (MockInterface $mock) {
            $mock->shouldReceive('updateItem')
                ->withSomeOfArgs('/me/todo/lists/1234')
                ->andReturn(new MicrosoftTodoList([
                    'id' => '1234',
                    'displayName' => 'General tasks',
                    'isOwner' => true,
                    'isShared' => false,
                    'wellKnownListName' => null,
                ]));
        }));

        $account = new IntegrationAccount(['account_name' => 'test@mail.com']);
        $repository = new MicrosoftTodoRepository($account);

        $list = $repository->updateTodoList(new TodoList([
            'id' => '1234',
            'name' => 'General tasks',
        ], $account));

        static::assertSame($account, $list->account);
        static::assertSame('test@mail.com::1234', $list->id);
        static::assertSame('General tasks', $list->name);
        static::assertFalse($list->isDefault);
        static::assertTrue($list->isOwner);
        static::assertFalse($list->isShared);
    }

    /**
     * A user can delete a list
     *
     * @test
     */
    public function a_user_can_delete_a_list(): void
    {
        $this->app->bind(MicrosoftGraphGateway::class, fn () => \Mockery::mock(MicrosoftGraphGateway::class, function (MockInterface $mock) {
            $mock->shouldReceive('deleteItem')
                ->withSomeOfArgs('/me/todo/lists/1234')
                ->andReturn(true);
        }));

        $account = new IntegrationAccount(['account_name' => 'test@mail.com']);
        $repository = new MicrosoftTodoRepository($account);

        $response = $repository->deleteTodoList('1234');

        static::assertTrue($response);
    }

    /**
     * A user can fetch their todos
     *
     * @test
     */
    public function a_user_can_fetch_their_todos(): void
    {
        $this->app->bind(MicrosoftGraphGateway::class, fn () => \Mockery::mock(MicrosoftGraphGateway::class, function (MockInterface $mock) {
            $promise = new Promise(function () use (&$promise) {
                $promise->resolve(new MicrosoftTodoList([
                    'id' => '1234',
                    'displayName' => 'General tasks',
                    'isOwner' => true,
                    'isShared' => false,
                    'wellKnownListName' => null,
                ]));
            }, fn () => null);

            $mock->shouldReceive('getItemAsync')
                ->withSomeOfArgs('/me/todo/lists/1234')
                ->andReturn($promise);

            $mock->shouldReceive('getCollection')
                ->withArgs(['/me/todo/lists/1234/tasks?$top=25&$skip=0',
                    MicrosoftTodoList::class,
                    '1234',
                    MicrosoftTodo::class]
                )
                ->andReturn([
                    new MicrosoftTodo([
                        'id' => 'test@mail.com::5678',
                        'title' => 'Do something',
                        'body' => ['content' => 'Something must be done'],
                        'importance' => 'high',
                        'lastModifiedDateTime' => '2022-08-09T00:00:00+00:00',
                        'dueDateTime' => ['dateTime' => '2022-08-09T00:00:00+00:00', 'timezone' => 'UTC'],
                        'completedDateTime' => ['dateTime' => '2022-08-09T00:00:00+00:00', 'timezone' => 'UTC'],
                        'recurrence' => [
                            'pattern' => [
                                'type' => 'weekly',
                                'interval' => 1,
                            ],
                        ],
                    ]),
                ]);

            $mock->shouldReceive('handleWaitPromise')
                ->with(\Mockery::type(PromiseInterface::class), MicrosoftTodoList::class, '1234')
                ->andReturnUsing(function ($promise) {
                    return $promise->wait();
                });
        }));

        $account = new IntegrationAccount(['account_name' => 'test@mail.com']);
        $repository = new MicrosoftTodoRepository($account);

        $todos = $repository->getTodos('1234');

        static::assertCount(1, $todos);
        /** @var \AccountIntegrations\Core\Todos\Todo $todo */
        $todo = $todos[0];
        static::assertSame($account, $todo->account);
        static::assertSame('test@mail.com::5678', $todo->id);
        static::assertSame('Do something', $todo->name);
        static::assertSame('Something must be done', $todo->description);
        static::assertSame(1, $todo->priority);
        static::assertSame('2022-08-09T00:00:00+00:00', $todo->updatedAt->toIso8601String());
        static::assertSame('2022-08-09T00:00:00+00:00', $todo->completedAt->toIso8601String());
        static::assertSame('2022-08-09T00:00:00+00:00', $todo->dueBy->toIso8601String());
        static::assertSame(['frequency' => 'WEEKLY', 'interval' => 1], $todo->recurrence);
    }
}
