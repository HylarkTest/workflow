<?php

declare(strict_types=1);

namespace AccountIntegrations\Core\Emails\Repositories;

use MarkupUtils\HTML;
use Sentry\State\Scope;
use Webklex\PHPIMAP\IMAP;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Webklex\PHPIMAP\Client;
use Webklex\PHPIMAP\Folder;
use Webklex\PHPIMAP\Message;
use Illuminate\Support\Carbon;
use Webklex\PHPIMAP\Query\Query;
use Illuminate\Support\Collection;

use function Sentry\configureScope;

use Webklex\PHPIMAP\Query\WhereQuery;
use Symfony\Component\Mime\DraftEmail;
use AccountIntegrations\Core\Emails\Email;
use AccountIntegrations\Core\Emails\Mailbox;
use Webklex\PHPIMAP\Support\MessageCollection;
use AccountIntegrations\Core\Emails\Attachment;
use Webklex\PHPIMAP\Attachment as ImapAttachment;
use AccountIntegrations\Models\IntegrationAccount;
use Webklex\PHPIMAP\Exceptions\AuthFailedException;
use Webklex\PHPIMAP\Connection\Protocols\ImapProtocol;
use AccountIntegrations\Exceptions\InvalidGrantException;
use Webklex\PHPIMAP\Exceptions\ConnectionFailedException;
use AccountIntegrations\Exceptions\ResourceNotFoundException;

/**
 * @phpstan-import-type AttachmentInfo from \AccountIntegrations\Core\Emails\Attachment
 * @phpstan-import-type EmailsFetchOptions from EmailRepository
 */
abstract class ImapRepository implements EmailRepository
{
    use SendsSmtpEmails;

    /**
     * @var \Illuminate\Support\Collection<string, \AccountIntegrations\Core\Emails\Mailbox>
     */
    protected Collection $mailboxes;

    public function __construct(protected Client $imapClient, protected IntegrationAccount $account)
    {
        try {
            $this->imapClient->connect();
        } catch (ConnectionFailedException $e) {
            if ($e->getPrevious() instanceof AuthFailedException) {
                throw new InvalidGrantException($this->account);
            }
            throw $e;
        }
    }

    public function __destruct()
    {
        $this->imapClient->disconnect();
    }

    public function getMailboxes(): Collection
    {
        return $this->imapClient
            ->getFolders(false)
            ->where('no_select', false)
            ->map(fn (Folder $folder) => $this->imapFolderToMailbox($folder))
            ->values();
    }

    public function cacheMailboxes(bool $force = false): void
    {
        if (isset($this->mailboxes) && ! $force) {
            return;
        }
        $this->mailboxes = cache()->remember(
            "account-integrations:google:mailboxes:$this->account->id",
            60,
            fn () => $this->getMailboxes()->keyBy->path(),
        );
    }

    public function getMailbox(string $mailboxId): Mailbox
    {
        if ($this->mailboxes[$mailboxId] ?? false) {
            return $this->mailboxes[$mailboxId];
        }

        return $this->imapFolderToMailbox($this->getImapFolderFromId($mailboxId));
    }

    public function createMailbox(Mailbox $mailbox): Mailbox
    {
        return $this->imapFolderToMailbox(
            $this->imapClient->createFolder($mailbox->name)
        );
    }

    public function updateMailbox(Mailbox $mailbox): Mailbox
    {
        /** @var \Webklex\PHPIMAP\Folder $folder */
        $folder = $this->imapClient->getFolderByPath($mailbox->id);
        $folder->rename($mailbox->name);

        return $mailbox;
    }

    public function deleteMailbox(string $mailboxId): bool
    {
        $this->getImapFolderFromId($mailboxId)->delete();

        return true;
    }

    /**
     * @param  EmailsFetchOptions  $options
     * @return \Illuminate\Support\Collection<int, \AccountIntegrations\Core\Emails\Email>
     *
     * @throws \Webklex\PHPIMAP\Exceptions\AuthFailedException
     * @throws \Webklex\PHPIMAP\Exceptions\ConnectionFailedException
     * @throws \Webklex\PHPIMAP\Exceptions\ImapBadRequestException
     * @throws \Webklex\PHPIMAP\Exceptions\ImapServerErrorException
     * @throws \Webklex\PHPIMAP\Exceptions\ResponseException
     * @throws \Webklex\PHPIMAP\Exceptions\RuntimeException
     */
    public function getEmailsSummary(?string $mailboxId = null, array $options = []): Collection
    {
        if (! $mailboxId) {
            throw new \Exception('A mailbox must be provided to get emails summary');
        }
        $folder = $this->getImapFolderFromId($mailboxId);
        $mailbox = $this->imapFolderToMailbox($folder);
        $query = $folder->messages()->leaveUnread();

        $ids = $this->getMessageIds($query, $mailbox, $options);

        $idsAreUids = ! ($options['search'] ?? false)
            && ! ($options['addresses'] ?? false)
            && ! ($options['ids'] ?? false);

        return \count($ids) ? $this->buildImapMessages($ids, $query->getClient(), $idsAreUids)->map(
            fn (Message $message) => $this->imapMessageToEmail($message, $mailbox)
        ) : new Collection;
    }

    /**
     * @param  EmailsFetchOptions  $options
     *
     * @throws \Webklex\PHPIMAP\Exceptions\AuthFailedException
     * @throws \Webklex\PHPIMAP\Exceptions\ConnectionFailedException
     * @throws \Webklex\PHPIMAP\Exceptions\ImapBadRequestException
     * @throws \Webklex\PHPIMAP\Exceptions\ImapServerErrorException
     * @throws \Webklex\PHPIMAP\Exceptions\ResponseException
     * @throws \Webklex\PHPIMAP\Exceptions\RuntimeException
     */
    public function getEmailsCount(?string $mailboxId = null, array $options = []): int
    {
        if (! $mailboxId) {
            throw new \Exception('A mailbox must be provided to get emails summary');
        }
        $folder = $this->getImapFolderFromId($mailboxId);
        $mailbox = $this->imapFolderToMailbox($folder);
        $query = $folder->messages()->leaveUnread();

        return count($this->getMessageIds($query, $mailbox, $options));
    }

    public function getEmail(string $emailId, ?string $mailboxId = null): Email
    {
        if (! $mailboxId) {
            throw new \Exception('A mailbox must be provided to get emails summary');
        }
        $folder = $this->getImapFolderFromId($mailboxId);
        $client = $folder->getClient();
        $client->openFolder($folder->path);
        $message = $this->getMessageFromUidOrMessageId($client, $emailId);

        return $this->imapMessageToEmail($message, $this->imapFolderToMailbox($folder));
    }

    /**
     * @param  AttachmentInfo[]  $attachments
     */
    public function sendEmail(Email $email, array $attachments = []): Email
    {
        if ($email->id) {
            $this->deleteEmail($this->getDraftMailboxId(), $email->id);
        }

        $email = $this->smtpSend($email, $attachments);

        $email->mailbox = $this->getMailbox($this->getSentMailboxId());

        return $email;
    }

    public function updateEmail(Email $email): Email
    {
        if (! $email->mailbox) {
            throw new \Exception('A mailbox must be provided to update an email');
        }
        $folder = $this->getImapFolderFromId((string) $email->mailbox->id);

        /** @var string $id */
        $id = $email->id;
        /** @var \Webklex\PHPIMAP\Query\WhereQuery $query */
        $query = $folder->messages()
            ->when(
                is_numeric($id),
                fn (WhereQuery $query) => $query->whereUid($id),
                fn (WhereQuery $query) => $query->whereMessageId($id)
            );

        /** @var \Webklex\PHPIMAP\Message $message */
        $message = $query->get()->first();

        if (isset($email->isFlagged)) {
            if ($email->isFlagged && $message->getFlags()->doesntContain('Flagged')) {
                $message->addFlag('Flagged');
            } elseif (! $email->isFlagged && $message->getFlags()->contains('Flagged')) {
                $message->removeFlag('Flagged');
            }
        }

        if (isset($email->isSeen)) {
            if ($email->isSeen && $message->getFlags()->doesntContain('Seen')) {
                $message->addFlag('Seen');
            } elseif (! $email->isSeen && $message->getFlags()->contains('Seen')) {
                $message->removeFlag('Seen');
            }
        }

        return $this->imapMessageToEmail($message, $email->mailbox);
    }

    public function deleteEmail(string $mailboxId, string $emailId): bool
    {
        $folder = $this->getImapFolderFromId($mailboxId);

        $query = $folder->messages()->leaveUnread();

        if (is_numeric($emailId)) {
            /** @var \Webklex\PHPIMAP\Message|null $message */
            $message = $this->buildImapMessages([$emailId], $query->getClient(), true)->first();
            $message?->setSequence(IMAP::SE_UID);
            $message?->setUid((int) $emailId);
        } else {
            /** @var \Webklex\PHPIMAP\Message|null $message */
            $message = $folder->messages()->whereMessageId($emailId)->get()->first();
        }

        if (! $message) {
            throw (new ResourceNotFoundException)->setIntegration($this->account, Mailbox::class, $mailboxId);
        }

        return $message->delete();
    }

    public function getAttachment(string $emailId, string $attachmentId, string $mailboxId): Attachment
    {
        if (Str::startsWith($attachmentId, 'cid:')) {
            $attachmentId = mb_substr($attachmentId, 4);
        }
        $email = $this->getEmail($emailId, $mailboxId);
        $attachments = $email->attachments;
        if (! $attachments) {
            throw new \RuntimeException('No attachments in the specified email');
        }
        /** @var \AccountIntegrations\Core\Emails\Attachment $attachment */
        $attachment = $attachments->first(fn (Attachment $attachment) => $attachmentId === $attachment->id);

        return $attachment;
    }

    public function saveDraft(Email $email, array $attachments = []): Email
    {
        $mailboxId = $this->getDraftMailboxId();

        $mailable = $this->buildMailableFromEmail($email, $attachments);

        /** @phpstan-ignore-next-line Method defined in custom class */
        $view = $mailable->getView();

        $plain = $view['text'] ?? null;
        $raw = $view['raw'] ?? null;
        $view = $view['html'] ?? null;

        $message = new \Illuminate\Mail\Message(new DraftEmail);

        /** @phpstan-ignore-next-line Method defined in custom class */
        $mailable->prepare($message);

        if (isset($view)) {
            $message->html($view->toHtml());
        }

        if (isset($plain)) {
            $message->text($plain);
        }

        if (isset($raw)) {
            $message->text($raw);
        }

        $symfonyMessage = $message->getSymfonyMessage();

        $folder = $this->getImapFolderFromId($mailboxId);

        $client = $folder->getClient();
        /** @var \Webklex\PHPIMAP\Connection\Protocols\ImapProtocol $imap */
        $imap = $client->getConnection();

        if ($email->id) {
            $this->deleteDraft($email->id);
        }

        $messageId = $this->addDraftToMailbox($imap, $symfonyMessage->toString());
        $email->id = $messageId;
        $email->mailbox = $this->imapFolderToMailbox($folder);

        return $email;
    }

    public function deleteDraft(string $emailId): bool
    {
        $mailboxId = $this->getDraftMailboxId();

        return $this->deleteEmail($mailboxId, $emailId);
    }

    /**
     * @return string[]
     *
     * @throws \Webklex\PHPIMAP\Exceptions\ConnectionFailedException
     * @throws \Webklex\PHPIMAP\Exceptions\RuntimeException
     */
    public function getMessageIds(Query $query, Mailbox $mailbox, array $args = []): array
    {
        $limit = $args['first'] ?? 100;
        $offset = $args['offset'] ?? 0;
        $search = $args['search'] ?? '';
        $addresses = $args['addresses'] ?? '';
        $ids = $args['ids'] ?? [];
        $onlyUnread = $args['unread'] ?? false;

        /** @var \Webklex\PHPIMAP\Connection\Protocols\ImapProtocol $imap */
        $imap = $query->getClient()->getConnection();

        $andFilters = [];
        $orFilters = [];

        // Seriously who came up with IMAP anyway?
        // Filtering with IMAP is very strange. There is no nesting.
        // By default, everything is filtered with AND.
        // You can use OR, but it only works for the next two filters in the array.
        // So if you want to OR more than two filters, you need to make the second
        // filter another OR.
        // So I'll give an example of what we are doing here:
        // - Unread should be AND
        // - Search should be done on FROM _OR_ SUBJECT, but ANDed with the rest
        // - Addresses and ids should all be ORed together, but ANDed with the rest
        // So a request for unread emails, searching for 'test', and from
        // 'me@example.com', or with the id '12345' would look like this:
        // ['UNSEEN', 'OR', 'FROM', '"test"', 'SUBJECT', '"test"', 'OR', 'FROM', '"me@example.com"', 'OR', 'TO', '"me@example.com"', 'OR', 'CC', '"me@example.com", 'OR', 'BCC', '"me@example.com"', 'HEADER Message-ID "12345"']
        //            |____________________OR____________________|                                                                                                  |____________________________OR____________________________|
        //                                                                                                                           |__________________________________________OR_____________________________________________|
        //                                                                                           |________________________________________________________OR_______________________________________________________________|
        //                                                         |________________________________________________________________________OR_________________________________________________________________________________|
        if ($onlyUnread) {
            $andFilters[] = 'UNSEEN';
        }
        if ($search) {
            $andFilters = [...$andFilters, 'OR', 'FROM', '"'.$search.'"', 'SUBJECT', '"'.$search.'"'];
        }
        if ($addresses) {
            foreach ($addresses as $address) {
                $orFilters = [...$orFilters, ['FROM', '"'.$address.'"'], ['TO', '"'.$address.'"'], ['CC', '"'.$address.'"'], ['BCC', '"'.$address.'"']];
            }
        }
        if ($ids) {
            $orFilters = [...$orFilters, ...array_map(fn ($id) => ["HEADER Message-ID '$id'"], $ids)];
        }

        if ($orFilters) {
            if (count($orFilters) > 1) {
                $andFilters = [...$andFilters, ...collect($orFilters)->flatMap(function ($search, $index) use ($orFilters) {
                    if ($index === count($orFilters) - 1) {
                        return $search;
                    }

                    return ['OR', ...$search];
                })->all()];
            } else {
                $andFilters[] = $orFilters[0];
            }
        }
        if ($andFilters) {
            /** @var array<int, array<string>> $response */
            $response = $imap->requestAndResponse('SEARCH', $andFilters)->array();

            return collect($response[0])->slice(1)->reverse()->skip($offset)->take($limit)->all();
        }

        $end = $mailbox->total;
        $end -= $offset;
        if ($end < 1) {
            return [];
        }
        $start = $end - ($limit - 1);
        $start = max($start, 1);
        $response = $imap->requestAndResponse('FETCH', ["$start:$end", '(UID)'])->array();

        return collect($response)->pluck('2.1')->slice(0, -1)->reverse()->filter()->values()->all();
    }

    protected function getMessageFromUidOrMessageId(Client $client, string|int $id): Message
    {
        if (is_numeric($id)) {
            $uid = $id;
        } else {
            /** @var \Webklex\PHPIMAP\Connection\Protocols\ImapProtocol $imap */
            $imap = $client->getConnection();
            $response = $imap->requestAndResponse('SEARCH', ["HEADER Message-ID '$id'"])->array();
            $uid = $response[0][1] ?? null;
        }
        if (! $uid) {
            throw (new ResourceNotFoundException)->setIntegration($this->account, Email::class, (string) $id);
        }

        /** @var \Webklex\PHPIMAP\Message $message */
        $message = $this->buildImapMessages([$uid], $client, is_numeric($id))->first();

        return $message;
    }

    /**
     * Now I want to use the package method `appendMessage` to save the draft,
     * but they recently updated that to only return a boolean, and I need
     * the full response in order to get the ID of the saved draft.
     * So this method is taken straight from the ImapProtocol class.
     *
     * @throws \Throwable
     * @throws \Webklex\PHPIMAP\Exceptions\RuntimeException
     */
    protected function addDraftToMailbox(ImapProtocol $imap, string $message): string
    {
        $tokens = [
            $imap->escapeString($this->getDraftMailboxId()),
            '(\Draft)',
            $imap->escapeString($message),
        ];

        $response = $imap->requestAndResponse('APPEND', $tokens, true)->array();

        $endOfResponse = Arr::last($response);
        throw_if(
            ! $endOfResponse || ! Str::startsWith($endOfResponse, 'OK'),
            new \RuntimeException('Could not save draft: '.json_encode($response))
        );

        $responseTokens = explode(' ', $endOfResponse);
        $idToken = $responseTokens[3];

        return mb_substr($idToken, 0, -1);
    }

    protected function buildRecipientList(array $recipients): string
    {
        return implode(', ', array_map(function (array $recipient) {
            if ($recipient['name']) {
                return $recipient['name'].' <'.$recipient['address'].'>';
            }

            return $recipient['address'];
        }, $recipients));
    }

    protected function getMailboxId(string $mailboxId): string
    {
        return Str::startsWith($mailboxId, $this->account->account_name.'::')
            ? mb_substr($mailboxId, mb_strlen($this->account->account_name) + 2)
            : $mailboxId;
    }

    protected function getImapFolderFromId(string $mailboxId): Folder
    {
        $path = $this->getMailboxId($mailboxId);

        $folder = $this->imapClient->getFolderByPath($path);

        if (! $folder) {
            throw (new ResourceNotFoundException)->setIntegration($this->account, Mailbox::class, $mailboxId);
        }

        return $folder;
    }

    /**
     * @param  string[]  $ids
     * @param  bool  $uid
     */
    protected function buildImapMessages(array $ids, Client $client, $uid = false): MessageCollection
    {
        /** @var \Webklex\PHPIMAP\Connection\Protocols\ImapProtocol $imap */
        $imap = $client->getConnection();
        $messages = MessageCollection::make();

        $rawMessages = $imap->requestAndResponse(($uid ? 'UID ' : '').'FETCH', [implode(',', $ids), '(UID FLAGS BODY.PEEK[HEADER] BODY.PEEK[TEXT])'])->array();

        collect($rawMessages)
            ->slice(0, -1)
            ->reverse()
            ->pluck('2')
            ->map(function (array $rawMessage): array {
                $body = [];
                for ($i = 0, $iMax = \count($rawMessage); $i < $iMax; $i += 2) {
                    $body[$rawMessage[$i]] = $rawMessage[$i + 1];
                }

                return $body;
            })
            ->sortBy(fn (array $rawMessage) => array_search($rawMessage['UID'], $ids, true))
            ->each(function (array $rawMessage) use ($messages, $client) {
                $reflection = new \ReflectionClass(Message::class);
                /** @var \Webklex\PHPIMAP\Message $message */
                $message = $reflection->newInstanceWithoutConstructor();
                $message->boot();

                configureScope(function (Scope $scope) use ($rawMessage): void {
                    $scope->setContext('email', ['raw' => $rawMessage]);
                });

                $default_mask = $client->getDefaultMessageMask();
                if ($default_mask !== null) {
                    $message->setMask($default_mask);
                }
                $message->setEvents([
                    'message' => $client->getDefaultEvents('message'),
                    'flag' => $client->getDefaultEvents('flag'),
                ]);

                $message->setFolderPath($client->getFolderPath());
                $message->setClient($client);
                $message->setFolderPath($client->getFolderPath());
                $message->parseRawHeader($rawMessage['BODY[HEADER]']);
                $message->parseRawFlags($rawMessage['FLAGS']);
                $message->parseRawBody($rawMessage['BODY[TEXT]']);
                $message->setSequence(IMAP::SE_UID);
                $message->setUid((int) $rawMessage['UID']);

                $messages->push($message);
            });

        configureScope(function (Scope $scope): void {
            $scope->removeContext('email');
        });

        return $messages;
    }

    protected function imapFolderToMailbox(Folder $folder): Mailbox
    {
        /*
         * The default PHP-IMAP implementation for getting unseen message counts
         * fetches an array of unread message ids and counts them. This doesn't
         * scale well, and ends up taking nearly twice as long as just
         * requesting the STATUS of the folder with the UNSEEN flag.
         * IMAP requests return an unusual structure. The status response looks
         * like this:
         * [
         *     [
         *         'STATUS',
         *         '{FOLDER_NAME}',
         *         [
         *             'MESSAGES',
         *             '{MESSAGES_COUNT}', // This is also what we want
         *             'UNSEEN',
         *             '{UNSEEN_COUNT}', // This is what we want
         *         ],
         *     ],
         *     [
         *         'OK',
         *         'Success',
         *     ],
         * ]
         */
        // $unseenCount = $folder->messages()->whereUnseen()->count();
        /** @var \Webklex\PHPIMAP\Connection\Protocols\ImapProtocol $connection */
        $connection = $this->imapClient->getConnection();
        $response = $connection->requestAndResponse('STATUS', ["\"$folder->path\"", '(MESSAGES UNSEEN)'])->array();

        $unseenCount = (int) ($response[0][2][3] ?? 0);
        $totalCount = (int) ($response[0][2][1] ?? 0);

        return new Mailbox([
            'id' => $this->account->account_name.'::'.$folder->path,
            'name' => $folder->full_name,
            'unseenCount' => $folder->path === $this->getDraftMailboxId() ? 0 : $unseenCount,
            'total' => $totalCount,
        ], $this->account);
    }

    protected function imapMessageToEmail(Message $message, Mailbox $mailbox): Email
    {
        $html = $message->getHTMLBody();
        $text = $message->getTextBody();

        if ($html) {
            $preview = mb_substr((string) (new HTML($html))->convertToPlaintext(), 0, 255);
        } else {
            $preview = mb_substr($text ?: '', 0, 255);
        }

        /** @phpstan-ignore-next-line  */
        $id = $message->getMessageId()?->toString() ?: (string) $message->getUid();
        $attachments = $message->attachments
            ->map(fn (ImapAttachment $attachment) => $this->buildAttachmentFromImapAttachment($attachment, $id, (string) $mailbox->id));

        return new Email([
            'id' => $id,
            /** @phpstan-ignore-next-line  */
            'internetMessageId' => $message->getMessageId()?->toString(),
            /** @phpstan-ignore-next-line  */
            'createdAt' => Carbon::parse($message->getDate()?->toDate()),
            'subject' => $message->getSubject()->toString(),
            'from' => $this->extractNameAndAddress($message->getFrom()->toString()),
            /** @phpstan-ignore-next-line  */
            'to' => collect($message->getTo()?->toArray() ?: [])->map(fn (string $address) => $this->extractNameAndAddress($address))->all(),
            /* @phpstan-ignore-next-line */
            'cc' => collect($message->getCc()?->toArray() ?: [])->map(fn (string $address) => $this->extractNameAndAddress($address))->all(),
            /* @phpstan-ignore-next-line */
            'bcc' => collect($message->getBcc()?->toArray() ?: [])->map(fn (string $address) => $this->extractNameAndAddress($address))->all(),
            'preview' => $preview,
            'html' => $html,
            'text' => $text,
            'isSeen' => $message->getFlags()->contains('Seen'),
            'isFlagged' => $message->getFlags()->contains('Flagged'),
            'isDraft' => $message->getFlags()->contains('Draft') || $mailbox->path() === $this->getDraftMailboxId(),
            'priority' => 0,
            'hasAttachments' => $attachments->filter(fn (Attachment $attachment) => ! $attachment->isInline)->isNotEmpty(),
            'attachments' => $attachments,
        ], $mailbox, $this->account);
    }

    protected function extractNameAndAddress(string $address): array
    {
        $name = null;
        $email = $address;

        if (preg_match('/^(.+) <(.+)>$/', $address, $matches)) {
            $name = $matches[1];
            $email = $matches[2];
        }

        return ['name' => $name, 'address' => $email];
    }

    protected function buildAttachmentFromImapAttachment(ImapAttachment $attachment, string $emailId, string $mailboxId): Attachment
    {
        $id = $attachment->getId() ?: (string) $attachment->getPartNumber();
        $isInline = $attachment->getDisposition() ? $attachment->getDisposition() === 'inline' : $attachment->getId() !== null;

        return new Attachment([
            'id' => $id,
            'contentId' => $attachment->getId(),
            'name' => $attachment->getName(),
            'content' => $attachment->getContent(),
            'fileType' => $attachment->getType(),
            'link' => route(
                'email-attachment-download-link',
                [
                    'accountId' => $this->account->id,
                    'mailboxId' => base64_encode($mailboxId),
                    'emailId' => base64_encode($emailId),
                    'attachmentId' => base64_encode($id),
                ]
            ),
            'isInline' => $isInline,
        ]);
    }

    abstract protected function getDraftMailboxId(): string;

    abstract protected function getSentMailboxId(): string;
}
