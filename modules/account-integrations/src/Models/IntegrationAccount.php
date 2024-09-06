<?php

declare(strict_types=1);

namespace AccountIntegrations\Models;

use Google\Client;
use Illuminate\Support\Arr;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use AccountIntegrations\Core\Scope;
use AccountIntegrations\Core\Provider;
use Illuminate\Database\Eloquent\Model;
use AccountIntegrations\Core\Todos\Todo;
use AccountIntegrations\Core\Emails\Email;
use GuzzleHttp\Exception\RequestException;
use LighthouseHelpers\Concerns\HasGlobalId;
use AccountIntegrations\Core\Calendar\Event;
use AccountIntegrations\Core\Emails\Mailbox;
use AccountIntegrations\Core\Todos\TodoList;
use AccountIntegrations\Core\Calendar\Calendar;
use AccountIntegrations\Core\Emails\Attachment;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use AccountIntegrations\Exceptions\InvalidGrantException;
use LighthouseHelpers\Concerns\ConvertsCamelCaseAttributes;
use AccountIntegrations\Core\Todos\Repositories\TodoRepository;
use AccountIntegrations\Core\Emails\Repositories\EmailRepository;
use AccountIntegrations\Http\Controllers\GoogleRedirectController;
use AccountIntegrations\Core\Todos\Repositories\GoogleTodoRepository;
use AccountIntegrations\Http\Controllers\MicrosoftRedirectController;
use AccountIntegrations\Core\Calendar\Repositories\CalendarRepository;
use AccountIntegrations\Core\Todos\Repositories\MovableTodoRepository;
use AccountIntegrations\Core\Emails\Repositories\GoogleEmailRepository;
use AccountIntegrations\Core\Todos\Repositories\MicrosoftTodoRepository;
use AccountIntegrations\Core\Calendar\Repositories\GoogleCalendarRepository;
use AccountIntegrations\Core\Calendar\Repositories\MicrosoftCalendarRepository;
use AccountIntegrations\Core\Emails\Repositories\MicrosoftGraphEmailRepository;

/**
 * Attributes
 *
 * @property int $id
 * @property string $account_name
 * @property \AccountIntegrations\Core\Provider $provider
 * @property \AccountIntegrations\Core\Scope[] $scopes
 * @property string $provider_id
 * @property string $token
 * @property string $refresh_token
 * @property int $account_owner_id
 * @property string $account_owner_type
 * @property \Illuminate\Support\Carbon $expires_at
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 *
 * Relationships
 * @property \Illuminate\Database\Eloquent\Model $accountOwner
 */
class IntegrationAccount extends Model
{
    use ConvertsCamelCaseAttributes;
    use HasGlobalId;

    protected Client $googleClient;

    protected $casts = [
        'provider' => Provider::class,
        'scopes' => 'array',
        'expires_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $fillable = [
        'provider',
        'scopes',
        'provider_id',
        'token',
        'refresh_token',
        'expires_at',
        'account_name',
    ];

    protected EmailRepository $emailRepository;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo<\Illuminate\Database\Eloquent\Model, \AccountIntegrations\Models\IntegrationAccount>
     */
    public function accountOwner(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Casts\Attribute<\AccountIntegrations\Core\Scope[], \AccountIntegrations\Core\Scope[]>
     */
    public function scopes(): Attribute
    {
        return Attribute::get(function (string|array $scopes): array {
            if (\is_string($scopes)) {
                $scopes = json_decode($scopes, true, 512, \JSON_THROW_ON_ERROR);
            }

            return array_map(fn (string $scope) => Scope::from($scope), $scopes);
        });
    }

    public function hasScope(Scope $scope): bool
    {
        return Arr::first($this->scopes, fn (Scope $scopeItem) => $scopeItem === $scope) !== null;
    }

    public function renewRedirectUrl(): string
    {
        return match ($this->provider) {
            Provider::GOOGLE => GoogleRedirectController::generateRedirectUrl($this),
            Provider::MICROSOFT => MicrosoftRedirectController::generateRedirectUrl($this),
        };
    }

    /**
     * @return \Illuminate\Support\Collection<int, \AccountIntegrations\Core\Todos\TodoList>
     *
     * @throws \Exception
     */
    public function getTodoLists(): Collection
    {
        return $this->todoRepository()->getTodoLists();
    }

    public function createTodoList(string $name): TodoList
    {
        $todoList = new TodoList(
            ['name' => $name],
            account: $this,
        );

        return $this->todoRepository()->createTodoList($todoList);
    }

    public function updateTodoList(string $listId, string $name): TodoList
    {
        $todoList = new TodoList(
            [
                'id' => $listId,
                'name' => $name,
            ],
            account: $this
        );

        return $this->todoRepository()->updateTodoList($todoList);
    }

    public function deleteTodoList(string $listId): bool
    {
        return $this->todoRepository()->deleteTodoList($listId);
    }

    /**
     * @return \Illuminate\Support\Collection<int, \AccountIntegrations\Core\Todos\Todo>
     *
     * @throws \Exception
     */
    public function getTodos(string $listId, array $options = []): Collection
    {
        return $this->todoRepository()->getTodos($listId, $options);
    }

    public function getTodo(string $listId, string $id): Todo
    {
        return $this->todoRepository()->getTodo($listId, $id);
    }

    public function createTodo(string $listId, array $options): Todo
    {
        $list = $this->todoRepository()->getTodoList($listId);
        $todo = new Todo($options, $list, $this);

        return $this->todoRepository()->createTodo($todo);
    }

    public function updateTodo(string $listId, array $options): Todo
    {
        $repository = $this->todoRepository();
        $list = $repository->getTodoList($listId);
        $todo = new Todo($options, $list, $this);

        return $repository->updateTodo($todo);
    }

    public function deleteTodo(string $listId, string $todoId): bool
    {
        return $this->todoRepository()->deleteTodo($listId, $todoId);
    }

    public function moveTodo(string $listId, string $todoId, ?string $previous = null, ?string $parent = null): Todo
    {
        $repository = $this->todoRepository();

        if ($repository instanceof MovableTodoRepository) {
            $list = $repository->getTodoList($listId);

            return $repository->moveTodo(new Todo(['id' => $todoId], $list, $this), $previous, $parent);
        }

        throw new \Exception("The provider {$this->provider->value} does not support moving Todos within a list");
    }

    /**
     * @return \Illuminate\Support\Collection<int, \AccountIntegrations\Core\Calendar\Calendar>
     *
     * @throws \Exception
     */
    public function getCalendars(): Collection
    {
        return $this->calendarRepository()->getCalendars();
    }

    public function createCalendar(string $name): Calendar
    {
        $calendar = new Calendar(
            ['name' => $name],
            account: $this,
        );

        return $this->calendarRepository()->createCalendar($calendar);
    }

    public function updateCalendar(string $calendarId, string $name, ?string $color): Calendar
    {
        $calendar = new Calendar(
            [
                'id' => $calendarId,
                'name' => $name,
                'color' => $color,
            ],
            account: $this
        );

        return $this->calendarRepository()->updateCalendar($calendar);
    }

    public function deleteCalendar(string $calendarId): bool
    {
        return $this->calendarRepository()->deleteCalendar($calendarId);
    }

    /**
     * @return \Illuminate\Support\Collection<int, \AccountIntegrations\Core\Calendar\Event>
     *
     * @throws \Exception
     */
    public function getEvents(string $calendarId, array $options = []): Collection
    {
        return $this->calendarRepository()->getEvents($calendarId, $options);
    }

    public function getEvent(string $calendarId, string $id): Event
    {
        return $this->calendarRepository()->getEvent($calendarId, $id);
    }

    /**
     * @return \Illuminate\Support\Collection<int, \AccountIntegrations\Core\Calendar\Event>
     *
     * @throws \Exception
     */
    public function getEventsBetween(string $calendarId, Carbon $start, Carbon $end, array $options = []): Collection
    {
        return $this->calendarRepository()->getEventsBetween($calendarId, $start, $end, $options);
    }

    public function createEvent(string $calendarId, array $options): Event
    {
        $calendar = $this->calendarRepository()->getCalendar($calendarId);
        $event = new Event($options, $calendar, $this);

        return $this->calendarRepository()->createEvent($event);
    }

    public function updateEvent(string $calendarId, array $options): Event
    {
        $repository = $this->calendarRepository();
        $calendar = $repository->getCalendar($calendarId);
        $event = new Event($options, $calendar, $this);

        return $repository->updateEvent($event);
    }

    public function deleteEvent(string $calendarId, string $eventId): bool
    {
        return $this->calendarRepository()->deleteEvent($calendarId, $eventId);
    }

    /**
     * @return \Illuminate\Support\Collection<int, \AccountIntegrations\Core\Emails\Mailbox>
     *
     * @throws \Exception
     */
    public function getMailboxes(): Collection
    {
        return $this->emailRepository()->getMailboxes();
    }

    public function getMailbox(string $mailboxId): Mailbox
    {
        return $this->emailRepository()->getMailbox($mailboxId);
    }

    public function createMailbox(string $name): Mailbox
    {
        $mailbox = new Mailbox(
            ['name' => $name],
            account: $this,
        );

        return $this->emailRepository()->createMailbox($mailbox);
    }

    public function updateMailbox(string $mailboxId, string $name): Mailbox
    {
        $mailbox = new Mailbox(
            [
                'id' => $mailboxId,
                'name' => $name,
            ],
            account: $this
        );

        return $this->emailRepository()->updateMailbox($mailbox);
    }

    public function deleteMailbox(string $mailboxId): bool
    {
        return $this->emailRepository()->deleteMailbox($mailboxId);
    }

    /**
     * @return \Illuminate\Support\Collection<int, \AccountIntegrations\Core\Emails\Email>
     *
     * @throws \Exception
     */
    public function getEmails(?string $mailboxId = null, array $options = []): Collection
    {
        return $this->emailRepository()->getEmailsSummary($mailboxId, $options);
    }

    public function getEmail(string $emailId, ?string $mailboxId = null): Email
    {
        return $this->emailRepository()->getEmail($emailId, $mailboxId);
    }

    public function createEmail(array $options, array $attachments): Email
    {
        $email = new Email($options, null, $this);

        return $this->emailRepository()->sendEmail($email, $attachments);
    }

    public function saveDraft(array $options, array $attachments): Email
    {
        $email = new Email($options, null, $this);

        return $this->emailRepository()->saveDraft($email, $attachments);
    }

    public function deleteDraft(string $emailId): bool
    {
        return $this->emailRepository()->deleteDraft($emailId);
    }

    public function updateEmail(string $mailboxId, array $options): Email
    {
        $mailbox = $this->emailRepository()->getMailbox($mailboxId);
        $email = new Email($options, $mailbox, $this);

        return $this->emailRepository()->updateEmail($email);
    }

    public function deleteEmail(string $mailboxId, string $emailId): bool
    {
        return $this->emailRepository()->deleteEmail($mailboxId, $emailId);
    }

    public function getAttachment(string $emailId, string $attachmentId, string $mailboxId): Attachment
    {
        return $this->emailRepository()->getAttachment($emailId, $attachmentId, $mailboxId);
    }

    public function accessTokenHasExpired(): bool
    {
        return $this->expires_at->subMinute()->isPast();
    }

    public function refreshToken(): string
    {
        if ($this->accessTokenHasExpired()) {
            match ($this->provider) {
                Provider::GOOGLE => $this->refreshGoogleToken(),
                Provider::MICROSOFT => $this->refreshMicrosoftToken(),
                // default => throw new \Exception("The provider {$this->provider->value} token cannot be refreshed"),
            };
        }

        return $this->token;
    }

    public function getGoogleClient(): Client
    {
        if ($this->provider !== Provider::GOOGLE) {
            throw new \Exception('Cannot build google client for integration with another provider');
        }

        if (! isset($this->googleClient)) {
            $this->googleClient = resolve(Client::class);

            $expiresAt = $this->expires_at;
            $this->googleClient->setAccessToken([
                'access_token' => $this->token,
                'refresh_token' => $this->refresh_token,
                'created' => $expiresAt->subHour()->timestamp,
                'expires_in' => 3600,
            ]);
        }

        return $this->googleClient;
    }

    public function emailRepository(): EmailRepository
    {
        if (isset($this->emailRepository)) {
            return $this->emailRepository;
        }

        $this->emailRepository = match ($this->provider) {
            Provider::GOOGLE => resolve(GoogleEmailRepository::class, [$this]),
            Provider::MICROSOFT => resolve(MicrosoftGraphEmailRepository::class, [$this]),
            // default => throw new \Exception("The provider {$this->provider->value} does not support Emails"),
        };

        return $this->emailRepository;
    }

    /**
     * @throws \Exception
     */
    public function getCalendarByName(string $name): ?Calendar
    {
        return $this->calendarRepository()->getCalendars()->firstWhere('name', $name);
    }

    protected function refreshGoogleToken(): string
    {
        $client = $this->getGoogleClient();
        $token = $client->refreshToken($this->refresh_token);
        if (($token['error'] ?? null) === 'invalid_grant') {
            throw new InvalidGrantException($this);
        }

        $this->token = $token['access_token'];
        $this->refresh_token = $token['refresh_token'];
        /** @var int $expiresAt */
        $expiresAt = $token['created'] + $token['expires_in'];
        $this->expires_at = Carbon::createFromTimestamp($expiresAt);

        $this->save();

        return $this->token;
    }

    protected function refreshMicrosoftToken(): string
    {
        $scopes = collect($this->scopes)
            ->flatMap(fn (Scope $scope) => MicrosoftRedirectController::SCOPES[$scope->name])
            ->push('offline_access')
            ->implode(' ');

        $client = new \GuzzleHttp\Client;

        try {
            $response = $client->post(
                'https://login.microsoftonline.com/'.
                config('account-integrations.azure.tenant', 'common').
                '/oauth2/v2.0/token?scope='.urlencode($scopes),
                [
                    RequestOptions::HEADERS => ['Accept' => 'application/json'],
                    RequestOptions::FORM_PARAMS => [
                        'client_id' => config('account-integrations.azure.client_id'),
                        'grant_type' => 'refresh_token',
                        'refresh_token' => $this->refresh_token,
                        'client_secret' => config('account-integrations.azure.client_secret'),
                    ],
                ]
            );
        } catch (RequestException $e) {
            if (\in_array($e->getCode(), [400, 401], true)) {
                throw new InvalidGrantException($this);
            }

            throw $e;
        }

        $response = json_decode($response->getBody()->getContents(), true, 512, \JSON_THROW_ON_ERROR);

        $this->update([
            'token' => $response['access_token'],
            'refresh_token' => $response['refresh_token'],
            'expires_at' => now()->addSeconds($response['expires_in']),
        ]);

        return $response['access_token'];
    }

    protected function todoRepository(): TodoRepository
    {
        return match ($this->provider) {
            Provider::GOOGLE => resolve(GoogleTodoRepository::class, [$this]),
            Provider::MICROSOFT => resolve(MicrosoftTodoRepository::class, [$this]),
            // default => throw new \Exception("The provider {$this->provider->value} does not support Todos"),
        };
    }

    protected function calendarRepository(): CalendarRepository
    {
        return match ($this->provider) {
            Provider::GOOGLE => resolve(GoogleCalendarRepository::class, [$this]),
            Provider::MICROSOFT => resolve(MicrosoftCalendarRepository::class, [$this]),
            // default => throw new \Exception("The provider {$this->provider->value} does not support Calendars"),
        };
    }
}
