<?php

declare(strict_types=1);

namespace App\Listeners\Auth;

use App\Events\Auth\PasswordUpdated;
use Illuminate\Auth\Events\PasswordReset;
use App\Notifications\Auth\PasswordChangedNotification;

class NotifyOnPasswordChange
{
    public function handle(PasswordUpdated|PasswordReset $event): void
    {
        $event->user->notify(new PasswordChangedNotification);
    }
}
