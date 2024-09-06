<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Channels\DatabaseChannel;

class GlobalNotificationChannel extends DatabaseChannel
{
    protected function buildPayload($notifiable, Notification $notification)
    {
        $payload = parent::buildPayload($notifiable, $notification);
        unset($payload['id']);
        if ($notification instanceof GlobalNotification) {
            $payload['global_notification_id'] = $notification->globalNotification->getKey();
        }

        return $payload;
    }
}
