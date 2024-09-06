<?php

declare(strict_types=1);

namespace App\Events\Billing;

use App\Models\User;

class SubscriptionDowngraded
{
    public function __construct(public User $user) {}
}
