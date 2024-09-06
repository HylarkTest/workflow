<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Models\Base;
use App\Models\User;
use App\Events\Auth\MemberLeft;
use App\Mail\BilledUserLeftBase;
use App\Events\Auth\OwnerRemoved;
use Laravel\Cashier\Subscription;
use Illuminate\Support\Facades\Mail;
use App\Events\Billing\AccountDeleted;

class CancelSubscriptions
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct() {}

    /**
     * @param  \App\Events\Billing\AccountDeleted|\App\Events\Auth\MemberLeft|"eloquent.deleting \App\Models\Base"  $event
     */
    public function handle(AccountDeleted|MemberLeft|OwnerRemoved|string $event, ?Base $base = null): void
    {
        if ($event instanceof AccountDeleted) {
            $subscriptions = $event->user->subscriptions;
        } elseif ($event instanceof MemberLeft || $event instanceof OwnerRemoved) {
            $subscriptions = $event->base->subscriptions
                ->where('user_id', $event->user->id);
        } else {
            if (! $base || $base->isPersonal()) {
                return;
            }
            $subscriptions = $base->subscriptions;
        }
        $subscriptions
            /** @phpstan-ignore-next-line This is the correct closure definition??? */
            ->filter(fn (Subscription $subscription) => $subscription->valid())
            /** @phpstan-ignore-next-line This is the correct closure definition??? */
            ->each(function (Subscription $subscription) use ($event) {
                try {
                    if ($event instanceof MemberLeft || $event instanceof OwnerRemoved) {
                        $subscription->cancel();
                        $event->base->owners()
                            ->each(fn (User $owner) => Mail::to($owner)->send(
                                new BilledUserLeftBase($owner, $event->base, $event->user)
                            ));
                    } else {
                        $subscription->cancelNow();
                    }
                } catch (\Exception $e) {
                    /** @var \App\Models\User $user */
                    $user = $subscription->owner;
                    logger()->error("Failed to unsubscribe user: {$user->name}");
                    report($e);
                }
            });
    }
}
