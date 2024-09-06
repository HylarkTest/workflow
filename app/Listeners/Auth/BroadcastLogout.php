<?php

declare(strict_types=1);

namespace App\Listeners\Auth;

use App\Events\Auth\LoggedOut;
use Illuminate\Auth\Events\Logout;

class BroadcastLogout
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function handle(Logout $event): void
    {
        /** @var \App\Models\User|null $user */
        $user = $event->user;

        // If the user is null, we can't broadcast anything as the user
        // info is needed to broadcast on a private channel.
        if (\is_null($user)) {
            return;
        }

        broadcast(new LoggedOut($user));
    }
}
