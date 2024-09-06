<?php

declare(strict_types=1);

namespace App\Events\Billing;

use App\Models\User;
use Laravel\Cashier\Subscription;

class SubscriptionCreated
{
    public function __construct(public User $user, public Subscription $subscription) {}
}
