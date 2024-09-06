<?php

declare(strict_types=1);

namespace App\Notifications\Auth;

use App\Models\LoginAttempt;
use Illuminate\Notifications\Notification;
use App\Core\Preferences\NotificationChannel;
use App\Notifications\DatabaseNotificationChannel;
use App\Notifications\Contracts\ChannelNotification;

class SuspiciousLoginNotification extends Notification implements ChannelNotification
{
    public function __construct(protected LoginAttempt $attempt) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array
     */
    public function via()
    {
        return [DatabaseNotificationChannel::class];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array
     */
    public function toDatabase()
    {
        return [
            'localized' => 'notifications/suspiciousLogin',
            'content' => [
                'params' => array_filter([
                    'browser' => $this->attempt->browser,
                    'device' => $this->attempt->device,
                    'ip' => $this->attempt->ip,
                ]),
            ],
        ];
    }

    public static function channel(): NotificationChannel
    {
        return NotificationChannel::ACCOUNT;
    }
}
