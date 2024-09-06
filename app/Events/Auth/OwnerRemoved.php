<?php

declare(strict_types=1);

namespace App\Events\Auth;

use App\Models\Base;
use App\Models\User;
use Illuminate\Queue\SerializesModels;

class OwnerRemoved
{
    use SerializesModels;

    public function __construct(public User $user, public Base $base) {}
}
