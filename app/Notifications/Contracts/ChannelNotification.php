<?php

declare(strict_types=1);

namespace App\Notifications\Contracts;

use App\Core\Preferences\NotificationChannel;

interface ChannelNotification
{
    public static function channel(): NotificationChannel;
}
