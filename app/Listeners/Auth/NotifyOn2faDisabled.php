<?php

declare(strict_types=1);

namespace App\Listeners\Auth;

use Laravel\Fortify\Events\TwoFactorAuthenticationDisabled;
use App\Notifications\Auth\TwoFactoryAuthenticationDisabledNotification;

class NotifyOn2faDisabled
{
    public function handle(TwoFactorAuthenticationDisabled $event): void
    {
        $event->user->notify(new TwoFactoryAuthenticationDisabledNotification);
    }
}
