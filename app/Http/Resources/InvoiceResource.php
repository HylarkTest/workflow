<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property int $id
 * @property string $transaction_id
 * @property string $status
 *
 * @mixin \Laravel\Cashier\Invoice
 */
class InvoiceResource extends JsonResource
{
    protected array $statusMap = [
        'draft' => 'pending',
        'open' => 'pending',
        'paid' => 'paid',
        'uncollectible' => 'failed',
        'void' => 'failed',
        'Completed' => 'paid',
        'Created' => 'pending',
        'Canceled' => 'cancelled',
        'Partially_Refunded' => 'paid',
        'Pending' => 'pending',
        'Refunded' => 'paid',
        'Denied' => 'failed',
        'Updated' => 'paid',
    ];

    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     */
    public function toArray($request): array
    {
        /** @var \App\Models\User $user */
        $user = $this->owner();
        /** @var \App\Models\BaseUserPivot|null $baseUser */
        $baseUser = $user->baseUsers()->where('base_id', tenant()->getKey())->first();

        return [
            'id' => $this->id ?: $this->transaction_id,
            'date' => (string) $this->date(),
            'amount' => $this->total(),
            'status' => $this->statusMap[$this->status],
            'billedUser' => $baseUser ? [
                'id' => $baseUser->global_id,
                'name' => $baseUser->displayName(),
                'avatar' => $baseUser->displayAvatar(),
                'email' => $user->email,
                'isAuthenticatedUser' => $user->is($request->user()),
            ] : null,
        ];
    }
}
