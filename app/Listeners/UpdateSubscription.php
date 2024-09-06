<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\Auth\MemberLeft;
use App\Events\Auth\MemberAccepted;
use Illuminate\Contracts\Queue\ShouldQueue;

class UpdateSubscription implements ShouldQueue
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct() {}

    public function handle(MemberAccepted|MemberLeft $event): void
    {
        if ($event instanceof MemberAccepted) {
            $base = $event->invite->base;
        } else {
            $base = $event->base;
        }

        $base->run(function () use ($base, $event) {
            if ($base->isPersonal() || ! $base->isSubscribed()) {
                return;
            }

            /** @var \Laravel\Cashier\Subscription $subscription */
            $subscription = $base->getActiveSubscription();

            $memberCount = $base->members()->count();

            /** @phpstan-ignore-next-line  */
            if ($event instanceof MemberLeft || $subscription->onTrial() || $subscription->quantity <= $memberCount) {
                $subscription->noProrate();
            }

            $subscription->updateQuantity($memberCount);
        });
    }
}
