<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\User;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class CompleteRegistration extends Notification
{
    public function via(): array
    {
        return ['mail'];
    }

    public function toMail(User $notifiable): MailMessage
    {
        /** @var string $subject */
        $subject = __('mail/completeRegistration.subject');

        return (new MailMessage)
            ->subject($subject)
            ->markdown('emails.complete-registration', [
                'fullName' => $notifiable->name,
            ]);
    }
}
