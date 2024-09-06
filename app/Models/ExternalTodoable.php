<?php

declare(strict_types=1);

namespace App\Models;

use AccountIntegrations\Models\IntegrationAccount;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Attributes
 *
 * @property int $id
 * @property string $todo_id
 * @property string $todo_list_id
 * @property int $integration_account_id
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 *
 * Relationships
 * @property \AccountIntegrations\Models\IntegrationAccount $integrationAccount
 */
class ExternalTodoable extends Model
{
    protected $fillable = [
        'todo_id',
        'todo_list_id',
        'integration_account_id',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo<\App\Models\Item|\App\Models\Note|\App\Models\Link|\App\Models\Todo|\App\Models\Event|\App\Models\Document|\App\Models\Pin, \App\Models\ExternalEventable>
     */
    public function todoable(): MorphTo
    {
        /** @phpstan-ignore-next-line Not sure how to get it matching parent type */
        return $this->morphTo();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\AccountIntegrations\Models\IntegrationAccount, \App\Models\ExternalTodoable>
     */
    public function integrationAccount(): BelongsTo
    {
        return $this->belongsTo(IntegrationAccount::class);
    }
}
