<?php

declare(strict_types=1);

namespace App\Events\Billing;

use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;

class AccountDeleted
{
    use Dispatchable;

    public function __construct(public User $user) {}
}
