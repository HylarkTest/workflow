<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\Auth\MemberLeft;
use Illuminate\Contracts\Queue\ShouldQueue;
use Nuwave\Lighthouse\Execution\Utils\Subscription;

class BroadcastMemberDeleted implements ShouldQueue
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct() {}

    public function handle(MemberLeft $event): void
    {
        Subscription::broadcast('memberDeleted', $event->base);
    }
}
