<?php

declare(strict_types=1);

namespace AccountIntegrations\Core\Emails;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use AccountIntegrations\Models\IntegrationAccount;

/**
 * Using dynamic properties so we can use `property_exists` to see if they've
 * been set, because `isset` ignores `null` values.
 *
 * @property ?string $html
 * @property ?string $text
 * @property ?string $preview
 * @property ?int $priority
 * @property ?bool $isFlagged
 * @property ?bool $isDraft
 * @property ?bool $isSeen
 * @property ?array{address: string, name?: string} $from
 * @property ?array{address: string, name?: string} $cc
 * @property ?array{address: string, name?: string} $bcc
 * @property ?bool $hasAttachments
 * @property ?\Illuminate\Support\Collection<int, \AccountIntegrations\Core\Emails\Attachment> $attachments
 */
#[\AllowDynamicProperties]
class Email
{
    public ?string $id;

    public ?string $internetMessageId;

    /**
     * @var array{address: string, name?: string}|null
     */
    public ?array $to;

    public ?string $subject;

    public ?Carbon $createdAt;

    public function __construct(array $eventArray, public ?Mailbox $mailbox, public IntegrationAccount $account)
    {
        $this->id = $eventArray['id'] ?? null;

        $this->internetMessageId = $eventArray['internetMessageId'] ?? null;

        $this->subject = $eventArray['subject'] ?? null;

        $this->to = $eventArray['to'] ?? null;

        $this->createdAt = $eventArray['createdAt'] ?? null;

        if (\array_key_exists('html', $eventArray)) {
            $this->html = $eventArray['html'];
        }
        if (\array_key_exists('text', $eventArray)) {
            $this->text = $eventArray['text'];
        }
        if (\array_key_exists('preview', $eventArray)) {
            $this->preview = $eventArray['preview'];
        }
        if (\array_key_exists('priority', $eventArray)) {
            $this->priority = $eventArray['priority'];
        }
        if (\array_key_exists('from', $eventArray)) {
            $this->from = $eventArray['from'];
        }
        if (\array_key_exists('cc', $eventArray)) {
            $this->cc = $eventArray['cc'];
        }
        if (\array_key_exists('bcc', $eventArray)) {
            $this->bcc = $eventArray['bcc'];
        }
        if (\array_key_exists('hasAttachments', $eventArray)) {
            $this->hasAttachments = $eventArray['hasAttachments'];
        }
        if (\array_key_exists('attachments', $eventArray)) {
            $this->attachments = $eventArray['attachments'];
        }
        if (\array_key_exists('isSeen', $eventArray)) {
            $this->isSeen = $eventArray['isSeen'];
        }
        if (\array_key_exists('isFlagged', $eventArray)) {
            $this->isFlagged = $eventArray['isFlagged'];
        }
        if (\array_key_exists('isDraft', $eventArray)) {
            $this->isDraft = $eventArray['isDraft'];
        }
    }

    /**
     * @return \Illuminate\Support\Collection<int, \AccountIntegrations\Core\Emails\Attachment>
     */
    public function explicitAttachments(): Collection
    {
        if (! $this->attachments) {
            return collect();
        }

        return $this->attachments->filter(fn (Attachment $attachment) => ! $attachment->isInline);
    }

    public function allIds(): array
    {
        $ids = [];
        if ($this->id) {
            $ids[] = $this->id;
        }
        if (isset($this->internetMessageId)) {
            $ids[] = $this->internetMessageId;
        }

        return $ids;
    }
}
