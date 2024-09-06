<?php

declare(strict_types=1);

namespace App\Notifications\Auth;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class TwoFactoryAuthenticationDisabledNotification extends Notification
{
    public function via(): array
    {
        return ['mail'];
    }

    /**
     * @param  \App\Models\User  $notifiable
     */
    public function toMail($notifiable): MailMessage
    {
        /** @var string $subject */
        $subject = __('mail/2faDisabled.subject');

        return (new MailMessage)
            ->subject($subject)
            ->markdown('emails.2fa-disabled', [
                'name' => $notifiable->name,
            ]);
    }
}
