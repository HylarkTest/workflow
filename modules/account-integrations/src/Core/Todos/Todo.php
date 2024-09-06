<?php

declare(strict_types=1);

namespace AccountIntegrations\Core\Todos;

use Illuminate\Support\Carbon;
use AccountIntegrations\Models\IntegrationAccount;

/**
 * Using dynamic properties so we can use `property_exists` to see if they've
 * been set, because `isset` ignores `null` values.
 *
 * @property ?string $order
 * @property ?string $description
 * @property ?\Illuminate\Support\Carbon $dueBy
 * @property ?\Illuminate\Support\Carbon $completedAt
 * @property ?array $recurrence
 * @property int $priority
 */
#[\AllowDynamicProperties]
class Todo
{
    public ?string $id;

    public ?string $name;

    public ?Carbon $updatedAt;

    public function __construct(array $todoArray, public TodoList $list, public IntegrationAccount $account)
    {
        $this->id = $todoArray['id'] ?? null;

        $this->name = $todoArray['name'] ?? null;

        $this->updatedAt = $todoArray['updatedAt'] ?? null;

        if (\array_key_exists('order', $todoArray)) {
            $this->order = $todoArray['order'];
        }
        if (\array_key_exists('description', $todoArray)) {
            $this->description = $todoArray['description'];
        }
        if (\array_key_exists('dueBy', $todoArray)) {
            $this->dueBy = $todoArray['dueBy'];
        }
        if (\array_key_exists('completedAt', $todoArray)) {
            $this->completedAt = $todoArray['completedAt'];
        }
        if (\array_key_exists('recurrence', $todoArray)) {
            $this->recurrence = $todoArray['recurrence'];
        }
        if (\array_key_exists('priority', $todoArray)) {
            $this->priority = $todoArray['priority'];
        }
    }
}
