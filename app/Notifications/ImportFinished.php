<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Import;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ImportFinished extends Notification
{
    public string $filename;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Import $import)
    {
        $this->filename = $import->filename;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array
     */
    public function via()
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(trans('mail/importFinished.subject'))
            ->markdown('emails.import-finished', [
                'filename' => $this->filename,
            ]);
    }
}
