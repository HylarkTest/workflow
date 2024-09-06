<?php

declare(strict_types=1);

namespace App\Core\Preferences;

enum NotificationChannel: string
{
    case ACCOUNT = 'ACCOUNT';
    case TIPS = 'TIPS';
    case NEW_FEATURES = 'NEW_FEATURES';
}
