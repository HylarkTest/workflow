<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\Auth\MemberAccepted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Nuwave\Lighthouse\Execution\Utils\Subscription;

class BroadcastMemberAdded implements ShouldQueue
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct() {}

    public function handle(MemberAccepted $event): void
    {
        Subscription::broadcast('memberAccepted', $event->invite->base);
    }
}
