<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\MemberInvite;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class CollabInvite extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public string $baseName;

    public ?string $inviterName;

    public ?string $baseImage;

    public string $inviteLink;

    public string $resendLink;

    public bool $resend;

    /**
     * Create a new message instance.
     */
    public function __construct(MemberInvite $invite, public bool $userExists)
    {
        $this->inviterName = $invite->base->members()->find($invite->inviter_id)?->baseDisplayName();
        $this->baseName = $invite->base->name;
        $this->baseImage = $invite->base->imageUrl;
        $this->inviteLink = $invite->getInviteLink();
        $this->resendLink = app('url')->signedRoute('member-invite.resend', ['invite' => $invite->id]);
        $this->resend = ! $invite->wasRecentlyCreated;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject(trans('mail/collab-invite.subject', ['name' => $this->baseName]))
            ->markdown('emails.collab-invite');
    }
}
