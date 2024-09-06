<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Mail\Downgrade;
use Illuminate\Support\Facades\Mail;
use App\Events\Billing\SubscriptionDowngraded;

class SendDowngradeEmails
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct() {}

    public function handle(SubscriptionDowngraded $event): void
    {
        $user = $event->user;

        Mail::to($user)->queue(new Downgrade($user));
    }
}
