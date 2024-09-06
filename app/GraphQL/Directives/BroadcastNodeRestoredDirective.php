<?php

declare(strict_types=1);

namespace App\GraphQL\Directives;

class BroadcastNodeRestoredDirective extends BroadcastNodeEventDirective
{
    protected static function getNodeEvent(): string
    {
        return 'nodeRestored';
    }
}
