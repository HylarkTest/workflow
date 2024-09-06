<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Base;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class TooManyBasesForInvite extends Notification implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    protected array $data;

    /**
     * Create a new message instance.
     */
    public function __construct(Base $base, User $inviter)
    {
        $this->data = [
            'inviterName' => $inviter->name,
            'baseName' => $base->name,
            'baseImage' => $base->imageUrl,
        ];
    }

    public function via(): array
    {
        return ['mail'];
    }

    public function toMail(): MailMessage
    {
        return (new MailMessage)
            ->subject(trans('mail/tooManyBasesForInvite.subject', ['name' => $this->data['baseName']]))
            ->markdown('emails.too-many-bases-for-invite', $this->data);
    }
}
