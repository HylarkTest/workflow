<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Mail\WelcomeToMembership;
use Illuminate\Support\Facades\Mail;
use App\Events\Billing\SubscriptionCreated;

class SendUpgradeEmails
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct() {}

    public function handle(SubscriptionCreated $event): void
    {
        $user = $event->user;
        $subscription = $event->subscription;

        Mail::to($user)->queue(new WelcomeToMembership($user, $subscription));
    }
}
