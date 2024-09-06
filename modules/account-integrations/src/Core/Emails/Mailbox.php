<?php

declare(strict_types=1);

namespace AccountIntegrations\Core\Emails;

use Illuminate\Support\Str;
use AccountIntegrations\Models\IntegrationAccount;

class Mailbox
{
    public ?string $id;

    public string $name;

    public int $unseenCount;

    public int $total;

    public bool $isCollapsed = false;

    public ?bool $isDefault;

    public function __construct(
        array $mailboxArray,
        public IntegrationAccount $account,
    ) {
        $this->id = $mailboxArray['id'] ?? null;
        $this->name = $mailboxArray['name'] ?? null;
        $this->unseenCount = $mailboxArray['unseenCount'] ?? 0;
        $this->total = $mailboxArray['total'] ?? 0;
        $this->isCollapsed = $mailboxArray['isCollapsed'] ?? false;
        $this->isDefault = $mailboxArray['isDefault'] ?? null;
    }

    public function path(): string
    {
        $id = (string) $this->id;

        return Str::startsWith($id, $this->account->account_name.'::')
            ? mb_substr($id, mb_strlen($this->account->account_name) + 2)
            : $id;
    }
}
