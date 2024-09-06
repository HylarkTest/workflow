<?php

declare(strict_types=1);

namespace AccountIntegrations\Core\Todos;

use Illuminate\Support\Carbon;
use AccountIntegrations\Models\IntegrationAccount;

class TodoList
{
    public ?bool $isOwner;

    public ?bool $isShared;

    public ?string $id;

    public string $name;

    public ?Carbon $updatedAt = null;

    public bool $isDefault = false;

    public function __construct(
        array $todoListArray,
        public IntegrationAccount $account,
    ) {
        $this->id = $todoListArray['id'] ?? null;
        $this->name = $todoListArray['name'] ?? null;
        $this->updatedAt = $todoListArray['updatedAt'] ?? null;
        if (isset($todoListArray['isShared'])) {
            $this->isShared = $todoListArray['isShared'];
        }
        $this->isDefault = $todoListArray['isDefault'] ?? false;
        if (isset($todoListArray['isOwner'])) {
            $this->isOwner = $todoListArray['isOwner'];
        }
    }
}
