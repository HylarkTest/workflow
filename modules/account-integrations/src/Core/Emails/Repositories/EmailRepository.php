<?php

declare(strict_types=1);

namespace AccountIntegrations\Core\Emails\Repositories;

use Illuminate\Support\Collection;
use AccountIntegrations\Core\Emails\Email;
use AccountIntegrations\Core\Emails\Mailbox;
use AccountIntegrations\Core\Emails\Attachment;

/**
 * @phpstan-type EmailsFetchOptions = array{
 *     first?: int,
 *     offset?: int,
 *     addresses?: string[],
 *     ids?: string[],
 *     search?: string,
 *     unread?: bool,
 * }
 */
interface EmailRepository
{
    /**
     * @return \Illuminate\Support\Collection<int, \AccountIntegrations\Core\Emails\Mailbox>
     */
    public function getMailboxes(): Collection;

    /**
     * Can be used to cache all mailboxes so if fetching emails across multiple
     * mailboxes we don't have to fetch each mailbox individually.
     */
    public function cacheMailboxes(bool $force = false): void;

    public function getMailbox(string $mailboxId): Mailbox;

    public function createMailbox(Mailbox $mailbox): Mailbox;

    public function updateMailbox(Mailbox $mailbox): Mailbox;

    public function deleteMailbox(string $mailboxId): bool;

    /**
     * @return \Illuminate\Support\Collection<int, \AccountIntegrations\Core\Emails\Email>
     */
    public function getEmailsSummary(?string $mailboxId = null, array $options = []): Collection;

    public function getEmailsCount(?string $mailboxId = null, array $options = []): int;

    public function getEmail(string $emailId, ?string $mailboxId = null): Email;

    public function sendEmail(Email $email, array $attachments): Email;

    public function saveDraft(Email $email, array $attachments): Email;

    public function deleteDraft(string $emailId): bool;

    public function updateEmail(Email $email): Email;

    public function deleteEmail(string $mailboxId, string $emailId): bool;

    public function getAttachment(string $mailboxId, string $emailId, string $attachmentId): Attachment;
}
