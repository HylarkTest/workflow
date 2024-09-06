<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Laravel\Cashier\Subscription;
use Illuminate\Support\Collection;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property string $name
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon|null $ends_at
 *
 * @mixin \Laravel\Cashier\Subscription
 */
class BillingResource extends JsonResource
{
    /**
     * @param  \Illuminate\Support\Collection<int, \Laravel\Cashier\Invoice>|null  $invoices
     */
    public function __construct(
        ?Subscription $subscription,
        protected ?Collection $invoices = null
    ) {
        parent::__construct($subscription);
    }

    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     */
    public function toArray($request): array
    {
        return [
            'isSubscribed' => $this->resource && $this->valid(),
            $this->mergeWhen($this->resource && $this->valid(), function () use ($request) {
                $subscription = $this->asStripeSubscription();

                /** @var \Stripe\Plan $plan */
                /** @phpstan-ignore-next-line */
                $plan = $subscription->plan;
                /** @phpstan-ignore-next-line */
                $amount = $plan->amount * $subscription->quantity;

                $discount = $subscription->discount;
                if ($discount) {
                    $amount = discounted_amount($amount, $discount->coupon, $plan);
                }

                /** @var \App\Models\User $user */
                $user = $this->owner;
                /** @var \App\Models\BaseUserPivot|null $baseUser */
                $baseUser = $user->baseUsers()->where('base_id', $this->getAttribute('base_id'))->first();

                return [
                    'name' => $this->name,
                    'billedUser' => $baseUser ? [
                        'id' => $baseUser->global_id,
                        'name' => $baseUser->displayName(),
                        'avatar' => $baseUser->displayAvatar(),
                        'email' => $user->email,
                        'isAuthenticatedUser' => $user->is($request->user()),
                    ] : null,
                    'subscribedAt' => $this->created_at,
                    'subscriptionEndsAt' => $this->ends_at ? (string) $this->ends_at : null,
                    'nextPaymentDate' => $this->when((bool) $this->invoices?->isNotEmpty(), function () use ($subscription) {
                        if ($subscription->cancel_at_period_end) {
                            return null;
                        }

                        return (string) Carbon::createFromTimestamp($subscription->current_period_end);
                    }),
                    'period' => $plan->interval,
                    'currency' => $plan->currency,
                    'amount' => $amount,
                    'discount' => $this->when($discount && is_discount_active($discount), function () use ($discount, $subscription) {
                        /** @var \Stripe\Discount $discount */
                        return [
                            'currency' => $discount->coupon->currency,
                            'amountOff' => $discount->coupon->amount_off,
                            'percentOff' => $discount->coupon->percent_off,
                            'duration' => $discount->coupon->duration_in_months,
                            'remaining' => $this->when((bool) $discount->end, function () use ($discount, $subscription) {
                                /** @var int $end */
                                $end = $discount->end;

                                return Carbon::createFromTimestamp($subscription->current_period_end)->subMonth()->diffInMonths(Carbon::createFromTimestamp($end));
                            }),
                        ];
                    }),
                ];
            }),
            'history' => $this->when((bool) $this->invoices?->isNotEmpty(), function () {
                return InvoiceResource::collection($this->invoices?->sortByDesc(static function ($invoice) {
                    return $invoice->date();
                }));
            }),
        ];
    }
}
