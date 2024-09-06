<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Base;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class BilledUserLeftBase extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public string $baseName;

    public string $fullName;

    public string $leftUser;

    public string $expiresAt;

    public string $link;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, Base $base, User $leftUser)
    {
        $this->baseName = $base->name;
        $this->fullName = $user->name;
        $this->leftUser = $leftUser->name;
        /** @phpstan-ignore-next-line */
        $this->expiresAt = $base->getActiveSubscription()->ends_at->format('F jS, Y');
        $this->link = url("/{$base->global_id}/settings/plans");
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject(trans('mail/billedUserLeftBase.subject', ['baseName' => $this->baseName]))
            ->markdown('emails.billed-user-left-base');
    }
}
