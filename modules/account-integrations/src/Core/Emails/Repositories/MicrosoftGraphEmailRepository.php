<?php

declare(strict_types=1);

namespace AccountIntegrations\Core\Emails\Repositories;

use Exception;
use Illuminate\Support\Str;
use GuzzleHttp\Promise\Utils;
use Illuminate\Support\Carbon;
use GuzzleHttp\Promise\Promise;
use Illuminate\Support\Collection;
use Microsoft\Graph\Model\Message;
use Microsoft\Graph\Model\BodyType;
use Microsoft\Graph\Model\ItemBody;
use Microsoft\Graph\Model\Recipient;
use Microsoft\Graph\Model\Importance;
use Microsoft\Graph\Model\MailFolder;
use Microsoft\Graph\Model\FollowupFlag;
use GuzzleHttp\Promise\PromiseInterface;
use Microsoft\Graph\Model\FileAttachment;
use AccountIntegrations\Core\Emails\Email;
use AccountIntegrations\Core\Emails\Mailbox;
use Microsoft\Graph\Model\FollowupFlagStatus;
use AccountIntegrations\Core\Emails\Attachment;
use AccountIntegrations\Models\IntegrationAccount;
use AccountIntegrations\Core\MicrosoftGraphGateway;
use AccountIntegrations\Core\Emails\Microsoft\DefaultMailbox;
use AccountIntegrations\Exceptions\ResourceNotFoundException;

/**
 * Class MicrosoftGraphEmailRepository
 *
 * We have some serious problems to solve with Microsoft Graph Mail API.
 * Specifically around the default folders and the way they are handled.
 * A Microsoft mail folder has four key pieces of information:
 * - The name
 * - The ID
 * - The well-known name for default folders
 * - Whether it is a default folder
 * Unfortunately the API only provides the first two pieces of information.
 * We need to find out the other two some other way.
 *
 * There is one way to know if a mail folder is a default folder, and that is
 * by fetching the default folders using their well known names instead of their
 * id. This means a separate request for each default folder.
 *
 * It doesn't take too long to fetch all the default folders individually, so
 * rather than making a command/job to cache them, we just fetch them in the first
 * request and cache them for subsequent requests.
 *
 * We can only cache the id as everything else is subject to change. And all we
 * need to use the cache for is to check if the folder is a default folder.
 *
 * @phpstan-import-type AttachmentInfo from \AccountIntegrations\Core\Emails\Attachment
 * @phpstan-import-type EmailsFetchOptions from EmailRepository
 */
class MicrosoftGraphEmailRepository implements EmailRepository
{
    use SendsSmtpEmails;

    /**
     * @var \Illuminate\Support\Collection<string, \AccountIntegrations\Core\Emails\Mailbox>
     */
    protected Collection $mailboxes;

    /**
     * @var array<string, string>
     */
    protected array $defaultMailboxMap;

    protected static array $commonFolderOrder = [
        DefaultMailbox::INBOX,
        DefaultMailbox::DRAFTS,
        '{FOLDERS}',
        DefaultMailbox::SENT_ITEMS,
        DefaultMailbox::ARCHIVE,
        DefaultMailbox::DELETED_ITEMS,
        DefaultMailbox::JUNK_EMAIL,
    ];

    protected static array $collapsedFolders = [
        DefaultMailbox::SENT_ITEMS,
        DefaultMailbox::ARCHIVE,
        DefaultMailbox::DELETED_ITEMS,
        DefaultMailbox::JUNK_EMAIL,
    ];

    protected static array $hiddenFolders = [
        DefaultMailbox::OUTBOX,
        DefaultMailbox::CONVERSATION_HISTORY,
        DefaultMailbox::CLUTTER,
    ];

    protected MicrosoftGraphGateway $gateway;

    public function __construct(protected IntegrationAccount $account)
    {
        $this->gateway = new MicrosoftGraphGateway($account);
        $this->mailboxes = collect();
    }

    /**
     * @return \Illuminate\Support\Collection<int, array{id: string, name: string}>
     */
    public function fetchAllDefaultMailboxInfo(): Collection
    {
        return collect(DefaultMailbox::cases())
            ->map(
                fn (DefaultMailbox $mailbox) => $this->gateway->getItemAsync("/me/mailFolders/$mailbox->value", MailFolder::class)
                    ->then(fn (?MailFolder $folder) => $folder ? [
                        'id' => $folder->getId(),
                        'name' => $mailbox->value,
                    ] : null)
            )
            /** @phpstan-ignore-next-line */
            ->pipe(fn (Collection $requests) => collect(Utils::all($requests->all())->wait()))
            ->filter();
    }

    /**
     * @return array<string, string>
     */
    public function defaultMailboxMap(): array
    {
        if (! isset($this->defaultMailboxMap)) {
            $key = "account-integrations:microsoft:default-mailboxes:{$this->account->id}";
            $this->defaultMailboxMap = cache()->rememberForever($key, function () {
                return $this->fetchAllDefaultMailboxInfo()->pluck('name', 'id')->all();
            });
        }

        return $this->defaultMailboxMap;
    }

    public function getDefaultNameFromId(string $id): ?DefaultMailbox
    {
        $name = $this->defaultMailboxMap()[$this->getMailboxId($id)] ?? null;

        return $name ? DefaultMailbox::from($name) : null;
    }

    public function getIdOfDefaultMailbox(DefaultMailbox $defaultMailbox): ?string
    {
        return array_search($defaultMailbox->value, $this->defaultMailboxMap()) ?: null;
    }

    /**
     * @param  \Illuminate\Support\Collection<int, \AccountIntegrations\Core\Emails\Mailbox>  $mailboxes
     * @return \Illuminate\Support\Collection<int, \AccountIntegrations\Core\Emails\Mailbox>
     */
    protected function ensureMailboxesIncludeCommonFolders(Collection $mailboxes): Collection
    {
        $mailboxesToLoad = collect();
        foreach (DefaultMailbox::commonMailboxes() as $defaultMailbox) {
            $commonMailboxId = $this->getIdOfDefaultMailbox($defaultMailbox);
            if (! $mailboxes->contains(fn (Mailbox $mailbox) => $mailbox->id && $this->getMailboxId($mailbox->id) === $commonMailboxId)) {
                $mailboxesToLoad->push($commonMailboxId);
            }
        }

        /** @var \AccountIntegrations\Core\Emails\Mailbox[] $loadedMailboxes */
        $loadedMailboxes = $mailboxesToLoad->isNotEmpty() ? Utils::all(
            $mailboxesToLoad->map(
                fn (string $mailboxId) => $this->getMailboxAsync($mailboxId)
            )->all()
        )->wait() : [];

        return $mailboxes->concat($loadedMailboxes);
    }

    public function getMailboxes(): Collection
    {
        // We fetch the top 100 and hope that contains the main default folder.
        // If not then we need to fetch them manually.
        $folders = $this->gateway->getCollection(
            '/me/mailFolders?$top=200',
            MailFolder::class,
            '',
            MailFolder::class
        );

        return collect($folders)
            ->filter(
                fn (MailFolder $folder) => ! \in_array(mb_strtolower((string) $folder->getDisplayName()), static::$hiddenFolders, true) && ! $folder->getIsHidden()
            )
            ->map(function (MailFolder $folder) {
                return $this->buildMailboxFromMicrosoftFolder($folder);
            })
            ->pipe(fn (Collection $mailboxes) => $this->ensureMailboxesIncludeCommonFolders($mailboxes))
            ->sortBy(function (Mailbox $mailbox) {
                $folderIndex = (string) array_search('{FOLDERS}', static::$commonFolderOrder, true);
                $defaultMailboxName = $mailbox->id ? $this->getDefaultNameFromId($mailbox->id) : null;
                $order = $defaultMailboxName
                    ? array_search($defaultMailboxName, static::$commonFolderOrder, true)
                    : false;

                if ($order === false) {
                    return $folderIndex.$mailbox->name;
                }

                return (string) $order;
            });
    }

    public function cacheMailboxes(bool $force = false): void
    {
        if (! $force && $this->mailboxes->count()) {
            return;
        }
        $this->mailboxes = cache()->remember(
            "account-integrations:microsoft:mailboxes:$this->account->id",
            60,
            fn () => $this->getMailboxes()->keyBy->path(),
        );
    }

    protected function getMailboxAsync(string $mailboxId): PromiseInterface
    {
        $mailboxId = $this->getMailboxId($mailboxId);

        if ($this->mailboxes[$mailboxId] ?? false) {
            $promise = new Promise;
            $promise->resolve($this->mailboxes[$mailboxId]);

            return $promise;
        }

        return $this->gateway->getItemAsync("/me/mailFolders/$mailboxId", MailFolder::class)
            ->then(function (MailFolder $folder) {
                $mailbox = $this->buildMailboxFromMicrosoftFolder($folder);

                $this->cacheMailbox($mailbox);

                return $mailbox;
            });
    }

    /**
     * @throws Exception
     */
    public function getMailbox(string $mailboxId): Mailbox
    {
        return $this->gateway->handleWaitPromise(
            $this->getMailboxAsync($mailboxId),
            Mailbox::class,
            $mailboxId
        );
    }

    /**
     * @throws \Microsoft\Graph\Exception\GraphException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \JsonException
     */
    public function createMailbox(Mailbox $mailbox): Mailbox
    {
        $response = $this->gateway->createItem(
            '/me/mailFolders',
            $this->buildMicrosoftFolderFromMailbox($mailbox),
            Mailbox::class,
            (string) $mailbox->id
        );

        return $this->buildMailboxFromMicrosoftFolder($response);
    }

    /**
     * @throws \Microsoft\Graph\Exception\GraphException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \JsonException
     */
    public function updateMailbox(Mailbox $mailbox): Mailbox
    {
        $response = $this->gateway->updateItem(
            "/me/mailFolders/$mailbox->id",
            $this->buildMicrosoftFolderFromMailbox($mailbox),
            Mailbox::class,
            (string) $mailbox->id
        );

        return $this->buildMailboxFromMicrosoftFolder($response);
    }

    /**
     * @throws \Exception
     */
    public function deleteMailbox(string $mailboxId): bool
    {
        $mailboxId = $this->getMailboxId($mailboxId);

        return $this->gateway->deleteItem(
            "/me/mailFolders/$mailboxId",
            Mailbox::class,
            $mailboxId
        );
    }

    /**
     * @param  EmailsFetchOptions  $options
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Microsoft\Graph\Exception\GraphException
     */
    public function getEmailsCount(?string $mailboxId = null, array $options = []): int
    {
        $mailboxId = $mailboxId ? $this->getMailboxId($mailboxId) : null;

        $url = $mailboxId ? "/me/mailFolders/$mailboxId/messages" : '/me/messages';

        $url = $this->buildUrlFromOptions($url, $options);

        return $this->gateway->createCollectionRequest($url, Message::class)->count();
    }

    /**
     * @param  EmailsFetchOptions  $options
     * @return \Illuminate\Support\Collection<int, \AccountIntegrations\Core\Emails\Email>
     */
    public function getEmailsSummary(?string $mailboxId = null, array $options = []): Collection
    {
        $mailboxId = $mailboxId ? $this->getMailboxId($mailboxId) : null;
        $summaryFields = implode(',', [
            'internetMessageId',
            'sender',
            'flag',
            'isDraft',
            'subject',
            'toRecipients',
            'from',
            'ccRecipients',
            'bccRecipients',
            'bodyPreview',
            'sentDateTime',
            'hasAttachments',
            'importance',
            'isRead',
            'parentFolderId',
            'receivedDateTime',
        ]);

        $url = $mailboxId ? "/me/mailFolders/$mailboxId/messages" : '/me/messages';

        $url = $this->buildUrlFromOptions($url, [
            ...$options,
            '$select' => $summaryFields,
        ]);

        $emails = $this->gateway->getCollection(
            $url,
            Mailbox::class,
            $mailboxId ?? '',
            Message::class,

        );

        return collect($emails)->map(function (Message $email) {
            return $this->buildEmailFromMicrosoftMessage($email);
        });
    }

    protected function emailHasAttachments(Message $email): bool
    {
        if ($email->getHasAttachments()) {
            return true;
        }
        $body = $email->getBody();

        return $body && $body->getContent() && preg_match('/<img[^>]+src="cid:/', $body->getContent());
    }

    public function getEmail(string $emailId, ?string $mailboxId = null): Email
    {
        $url = $mailboxId ? "/me/mailFolders/$mailboxId/messages" : '/me/messages';
        if (preg_match('/<(.*)>/', $emailId)) {
            $url .= "?\$filter=InternetMessageId eq '$emailId'";
            $emails = $this->gateway->getCollection(
                $url,
                Email::class,
                $emailId,
                Message::class,
            );
            /** @var \Microsoft\Graph\Model\Message|null $email */
            $email = $emails[0];
        } else {
            /** @var \Microsoft\Graph\Model\Message|null $email */
            $email = $this->gateway->getItem(
                "$url/$emailId",
                Message::class,
                $emailId,
                Message::class
            );
        }

        if (! isset($email)) {
            throw (new ResourceNotFoundException)->setIntegration($this->account, Email::class, $emailId);
        }

        if ($this->emailHasAttachments($email)) {
            /** @var string $id */
            $id = $email->getId();
            $attachments = collect($this->getAndCacheAttachments($id));
            $attachments = $attachments
                ->map(function (FileAttachment $attachment) use ($email, $id) {
                    /** @var string $mailboxId */
                    $mailboxId = $email->getParentFolderId();

                    return $this->buildAttachmentFromMicrosoftFileAttachment($attachment, $id, $mailboxId);
                });
        } else {
            $attachments = collect();
        }

        return $this->buildEmailFromMicrosoftMessage($email, $attachments);
    }

    /**
     * @param  AttachmentInfo[]  $attachments
     *
     * @throws \Microsoft\Graph\Exception\GraphException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \JsonException
     * @throws \Exception
     */
    public function sendEmail(Email $email, array $attachments): Email
    {
        if (config('account-integrations.trap-emails')) {
            $email = $this->smtpSend($email);
        } else {
            $email = $this->saveDraft($email, $attachments);

            $this->gateway->sendRequest(
                '/me/messages/'.$email->id.'/send',
                Email::class,
                (string) $email->id
            );
            $email->id = $email->internetMessageId;
        }
        $email->mailbox = $this->getSentMailbox();

        return $email;
    }

    public function updateEmail(Email $email): Email
    {
        if (! $email->mailbox) {
            throw new Exception('A mailbox must be provided to update an email');
        }
        $message = new Message([]);
        $message->setId((string) $email->id);
        if (isset($email->isSeen)) {
            $message->setIsRead($email->isSeen);
        }
        if (isset($email->priority)) {
            $message->setImportance(new Importance(
                match ($email->priority) {
                    1,2,3,4 => Importance::HIGH,
                    5,6,7,8 => Importance::NORMAL,
                    default => Importance::LOW,
                }
            ));
        }
        if (isset($email->isFlagged)) {
            $message->setFlag(new FollowupFlag(['flagStatus' => new FollowupFlagStatus($email->isFlagged ? FollowupFlagStatus::FLAGGED : FollowupFlagStatus::NOT_FLAGGED)]));
        }
        $response = $this->gateway->updateItem(
            '/me/mailFolders/'.$email->mailbox->id.'/messages/'.$message->getId(),
            $message,
            Email::class,
            (string) $email->id
        );

        $this->cacheMailbox($email->mailbox);

        return $this->buildEmailFromMicrosoftMessage($response);
    }

    /**
     * @throws \Exception
     */
    public function deleteEmail(string $mailboxId, string $emailId): bool
    {
        $mailboxId = $this->getMailboxId($mailboxId);

        return $this->gateway->deleteItem(
            "/me/mailFolders/$mailboxId/messages/$emailId",
            Email::class,
            $emailId
        );
    }

    public function getAttachment(string $emailId, string $attachmentId, string $mailboxId): Attachment
    {
        $mailboxId = $this->getMailboxId($mailboxId);
        if (Str::startsWith($attachmentId, 'cid:')) {
            $attachmentId = mb_substr($attachmentId, 4);
        }
        /** @var \Microsoft\Graph\Model\FileAttachment|null $attachment */
        $attachment = collect($this->getAndCacheAttachments($emailId))
            ->first(fn (FileAttachment $attachment) => match ($attachmentId) {
                $attachment->getId(),
                $attachment->getContentId() => true,
                default => false,
            });

        if (! $attachment) {
            throw (new ResourceNotFoundException)->setIntegration($this->account, Attachment::class, $attachmentId);
        }

        return $this->buildAttachmentFromMicrosoftFileAttachment($attachment, $emailId, $mailboxId);
    }

    public function buildMicrosoftMessageFromEmail(Email $email): Message
    {
        $message = new Message([]);
        if ($email->id) {
            $message->setId($email->id);
        }
        if ($email->to) {
            $message->setToRecipients(array_map(fn (string $to) => new Recipient(['emailAddress' => ['address' => $to]]), $email->to));
        }
        if ($email->cc ?? null) {
            $message->setCcRecipients(array_map(fn (string $cc) => new Recipient(['emailAddress' => ['address' => $cc]]), $email->cc));
        }
        if ($email->bcc ?? null) {
            $message->setBccRecipients(array_map(fn (string $bcc) => new Recipient(['emailAddress' => ['address' => $bcc]]), $email->bcc));
        }
        if ($email->subject) {
            $message->setSubject($email->subject);
        }
        if ($email->html ?? null) {
            $message->setBody(new ItemBody([
                'contentType' => BodyType::HTML,
                'content' => $email->html,
            ]));
        } elseif ($email->text ?? null) {
            $message->setBody(new ItemBody([
                'contentType' => BodyType::TEXT,
                'content' => $email->text,
            ]));
        }

        return $message;
    }

    /**
     * @param  AttachmentInfo[]  $attachments
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \JsonException
     * @throws \Microsoft\Graph\Exception\GraphException
     */
    public function saveDraft(Email $email, array $attachments): Email
    {
        $message = $this->buildMicrosoftMessageFromEmail($email);

        if ($email->id) {
            $url = "/me/messages/$email->id";
            $message = $this->gateway->updateItem(
                $url,
                $message,
                Email::class,
                $email->id
            );
            $oldAttachments = collect($this->gateway->getCollection(
                "$url/attachments",
                Email::class,
                $email->id,
                FileAttachment::class
            ));
            $oldAttachments->each(/**
             * @throws \Exception
             */ function (FileAttachment $attachment) use ($url, $email) {
                $this->gateway->deleteItem(
                    "$url/attachments/".$attachment->getId(),
                    Email::class,
                    $email->id
                );
            });
        } else {
            $message = $this->gateway->createItem(
                '/me/messages/',
                $message,
                Email::class,
                (string) $email->id
            );
        }

        foreach ($attachments as $attachment) {
            $file = $attachment['file'];
            $name = $attachment['name'] ?? $file->getClientOriginalName();
            $this->gateway->sendRequest(
                '/me/messages/'.$message->getId().'/attachments',
                Email::class,
                (string) $email->id,
                [
                    '@odata.type' => '#microsoft.graph.fileAttachment',
                    'name' => $name,
                    'contentType' => $file->getMimeType(),
                    'contentBytes' => base64_encode($file->getContent()),
                    'isInline' => $attachment['isInline'],
                    'contentId' => $attachment['contentId'] ?? null,
                ]
            );
        }

        $email->mailbox = $this->getDraftMailbox();
        $email->id = $message->getId();
        $email->internetMessageId = $message->getInternetMessageId();

        return $email;
    }

    /**
     * @throws \Exception
     */
    public function deleteDraft(string $emailId): bool
    {
        return $this->gateway->deleteItem(
            "/me/messages/$emailId",
            Email::class,
            $emailId
        );
    }

    protected function getDraftMailbox(): Mailbox
    {
        return $this->getMailbox(DefaultMailbox::DRAFTS->value);
    }

    protected function getSentMailbox(): Mailbox
    {
        return $this->getMailbox(DefaultMailbox::SENT_ITEMS->value);
    }

    /**
     * @return \Microsoft\Graph\Model\FileAttachment[]
     */
    protected function getAndCacheAttachments(string $emailId): array
    {
        // Microsoft limits requests to 4 of the same requests every 10
        // minutes. So if an email has over 4 inline attachments, this will
        // exceed the limit as we need to fetch all attachments each time.
        // To avoid this, we cache the attachments for 10 minutes.
        /** @var \Microsoft\Graph\Model\FileAttachment[] $attachments */
        $attachments = cache()->remember("microsoft:mail:attachments:$emailId", now()->addMinutes(10), function () use ($emailId) {
            return $this->gateway->getCollection(
                "/me/messages/$emailId/attachments",
                Email::class,
                $emailId,
                FileAttachment::class

            );
        });

        return $attachments;
    }

    protected function buildUrlFromOptions(string $baseUrl, array $options): string
    {
        $first = $options['first'] ?? 50;
        $offset = $options['offset'] ?? 0;

        $query = [
            '$top' => $first,
            '$skip' => $offset,
            ...collect($options)->filter(fn ($_, $key) => str_starts_with($key, '$'))->toArray(),
        ];

        $andFilters = [];
        $orFilters = [];

        if ($options['unread'] ?? false) {
            $andFilters[] = 'isRead eq false';
        }
        if ($options['search'] ?? null) {
            $search = str_replace('\'', '\'\'', $options['search']);
            $andFilters[] = '('.implode(' or ', [
                "contains(subject, '$search')",
                "contains(from/emailAddress/name, '$search')",
                "contains(from/emailAddress/address, '$search')",
            ]).')';
        }
        if ($options['addresses'] ?? null) {
            foreach ($options['addresses'] as $address) {
                $address = str_replace('\'', '\'\'', $address);
                $orFilters = [...$orFilters, "from/emailAddress/address eq '$address'"];
            }
        }
        if ($options['ids'] ?? null) {
            $orFilters = [...$orFilters, ...array_map(fn (string $id) => "internetMessageId eq '$id'", $options['ids'])];
        }

        if ($orFilters) {
            $andFilters[] = '('.implode(' or ', $orFilters).')';
        }
        if ($andFilters) {
            $tomorrow = Carbon::tomorrow()->format('Y-m-d');
            $query['$filter'] = "receivedDateTime lt $tomorrow and ".implode(' and ', $andFilters);
        }
        $query['$orderby'] = 'receivedDateTime desc';

        return "$baseUrl?".http_build_query($query);
    }

    protected function cacheMailbox(Mailbox $mailbox): void
    {
        /** @var string $id */
        $id = $mailbox->id;
        $this->mailboxes[$this->getMailboxId($id)] = $mailbox;
    }

    protected function getMailboxId(string $mailboxId): string
    {
        return Str::startsWith($mailboxId, $this->account->account_name.'::')
            ? mb_substr($mailboxId, mb_strlen($this->account->account_name) + 2)
            : $mailboxId;
    }

    protected function buildAttachmentFromMicrosoftFileAttachment(FileAttachment $fileAttachment, string $emailId, string $mailboxId): Attachment
    {
        $mailboxId = $this->getMailboxId($mailboxId);

        return new Attachment([
            'id' => $fileAttachment->getId(),
            'contentId' => $fileAttachment->getContentId(),
            'name' => $fileAttachment->getName(),
            'content' => $fileAttachment->getContentBytes(),
            'fileType' => $fileAttachment->getContentType(),
            'link' => route(
                'email-attachment-download-link',
                [
                    'accountId' => $this->account->id,
                    'mailboxId' => base64_encode($mailboxId),
                    'emailId' => base64_encode($emailId),
                    'attachmentId' => base64_encode((string) $fileAttachment->getId()),
                ]
            ),
            'isInline' => $fileAttachment->getIsInline(),
        ]);
    }

    protected function buildMailboxFromMicrosoftFolder(MailFolder $folder): Mailbox
    {
        $id = $folder->getId();
        $defaultName = $id ? $this->getDefaultNameFromId($id) : null;

        return new Mailbox([
            'id' => $this->account->account_name.'::'.$folder->getId(),
            'name' => $folder->getDisplayName(),
            'unseenCount' => $folder->getUnreadItemCount(),
            'total' => $folder->getTotalItemCount(),
            'isCollapsed' => $defaultName && \in_array($defaultName, static::$collapsedFolders, true),
            'isDefault' => $defaultName !== null,
        ], $this->account);
    }

    protected function buildMicrosoftFolderFromMailbox(Mailbox $mailbox): MailFolder
    {
        $folder = new MailFolder;

        if ($mailbox->id) {
            $folder->setId($mailbox->path());
        }
        $folder->setDisplayName($mailbox->name);

        return $folder;
    }

    /**
     * @param  \Illuminate\Support\Collection<int, \AccountIntegrations\Core\Emails\Attachment>|null  $attachments
     */
    protected function buildEmailFromMicrosoftMessage(Message $message, ?Collection $attachments = null): Email
    {
        $body = $message->getBody();
        if ($body) {
            /** @var string $html */
            $html = $body->getContentType()?->is(BodyType::HTML) ? $body->getContent() : '<p>'.$body->getContent().'</p>';
            $text = $body->getContentType()?->is(BodyType::HTML) ? strip_tags($body->getContent() ?: '') : $body->getContent();
        } else {
            $html = '';
            $text = '';
        }

        $importance = $message->getImportance();

        $createdAt = $message->getSentDateTime();

        /** @var string $mailboxId */
        $mailboxId = $message->getParentFolderId();
        $mailbox = $this->getMailbox($mailboxId);

        // Make sure you include the correct fields in the $summaryFields variable
        // if you want them to appear when fetching the emails summary.
        return new Email([
            'id' => $message->getId(),
            'internetMessageId' => $message->getInternetMessageId(),
            'to' => collect($message->getToRecipients())->map(fn (array|Recipient $recipient) => $recipient instanceof Recipient ? $recipient->getEmailAddress()?->getProperties() : $recipient['emailAddress'])->all(),
            'from' => $message->getFrom()?->getEmailAddress()?->getProperties(),
            'cc' => collect($message->getCcRecipients())->map(fn (array|Recipient $recipient) => $recipient instanceof Recipient ? $recipient->getEmailAddress()?->getProperties() : $recipient['emailAddress'])->all(),
            'bcc' => collect($message->getBccRecipients())->map(fn (array|Recipient $recipient) => $recipient instanceof Recipient ? $recipient->getEmailAddress()?->getProperties() : $recipient['emailAddress'])->all(),
            'subject' => $message->getSubject(),
            'preview' => $message->getBodyPreview(),
            'text' => $text,
            'html' => $html,
            'isSeen' => $message->getIsRead(),
            'hasAttachments' => $message->getHasAttachments(),
            'attachments' => $attachments,
            'priority' => match (true) {
                $importance?->is(Importance::HIGH) => 1,
                $importance?->is(Importance::NORMAL) => 5,
                $importance?->is(Importance::LOW) => 9,
                default => 0
            },
            'isFlagged' => (bool) $message->getFlag()?->getFlagStatus()?->is(FollowupFlagStatus::FLAGGED),
            'isDraft' => $message->getIsDraft(),
            'createdAt' => $createdAt ? Carbon::parse($createdAt) : null,
        ], $mailbox, $this->account);
    }
}
