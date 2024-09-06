<?php

declare(strict_types=1);

namespace App\Listeners\Auth;

use Illuminate\Auth\Events\Verified;
use Illuminate\Auth\Events\PasswordReset;

class VerifiesEmailOnPasswordReset
{
    public function handle(PasswordReset $event): void
    {
        /** @var \App\Models\User $user */
        $user = $event->user;
        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }
    }
}
