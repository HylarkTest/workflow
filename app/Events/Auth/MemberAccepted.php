<?php

declare(strict_types=1);

namespace App\Events\Auth;

use App\Models\MemberInvite;
use Illuminate\Queue\SerializesModels;

class MemberAccepted
{
    use SerializesModels;

    public function __construct(public MemberInvite $invite) {}
}
