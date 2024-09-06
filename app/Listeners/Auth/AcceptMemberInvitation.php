<?php

declare(strict_types=1);

namespace App\Listeners\Auth;

use App\Models\MemberInvite;
use Illuminate\Auth\Events\Login;

class AcceptMemberInvitation
{
    public function handle(Login $event): void
    {
        $inviteId = session()->pull('member-invite');

        /** @var \App\Models\MemberInvite|null $invite */
        $invite = $inviteId ? MemberInvite::find($inviteId) : null;

        if ($invite) {
            /** @var \App\Models\User $user */
            $user = $event->user;
            if (strcasecmp($invite->email, $user->email) === 0) {
                $user->acceptMemberInvite($invite);
                $user->setActiveBase($invite->base);
            }
        }
    }
}
