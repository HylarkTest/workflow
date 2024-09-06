<?php

declare(strict_types=1);

namespace AccountIntegrations\Core\Calendar;

use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use AccountIntegrations\Models\IntegrationAccount;

class Calendar
{
    public ?bool $isOwner;

    public ?bool $isShared;

    public ?string $id;

    public string $name;

    public ?string $color;

    public ?Carbon $updatedAt = null;

    public bool $isDefault = false;

    public bool $isReadOnly = false;

    public function __construct(
        array $calendarArray,
        public IntegrationAccount $account,
    ) {
        $this->id = $calendarArray['id'] ?? null;
        $this->name = $calendarArray['name'] ?? null;
        $this->isReadOnly = $calendarArray['isReadOnly'] ?? false;
        $this->updatedAt = $calendarArray['updatedAt'] ?? null;
        if (isset($calendarArray['isShared'])) {
            $this->isShared = $calendarArray['isShared'];
        }
        $this->isDefault = $calendarArray['isDefault'] ?? false;
        if (isset($calendarArray['isOwner'])) {
            $this->isOwner = $calendarArray['isOwner'];
        }
        if (isset($calendarArray['color'])) {
            $this->color = $calendarArray['color'];
        }
    }

    public function baseId(): string
    {
        $id = (string) $this->id;

        return Str::startsWith($id, $this->account->account_name.'::')
            ? mb_substr($id, mb_strlen($this->account->account_name) + 2)
            : $id;
    }
}
