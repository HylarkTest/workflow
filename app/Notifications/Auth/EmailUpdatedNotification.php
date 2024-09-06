<?php

declare(strict_types=1);

namespace App\Notifications\Auth;

use App\Models\User;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Notifications\Contracts\CustomEmailNotification;

class EmailUpdatedNotification extends Notification implements CustomEmailNotification
{
    public function __construct(protected User $user, protected string $emailAddress)
    {
        //
    }

    public function getEmailAddress(): string
    {
        return $this->emailAddress;
    }

    public function via(): array
    {
        return ['mail'];
    }

    public function toMail(): MailMessage
    {
        /** @var string $subject */
        $subject = __('mail/emailUpdated.subject');

        return (new MailMessage)
            ->subject($subject)
            ->markdown('emails.email-updated', [
                'name' => $this->user->name,
                'supportEmail' => config('mail.from.support'),
                'newEmail' => $this->user->email,
                'oldEmail' => $this->emailAddress,
            ]);
    }
}
