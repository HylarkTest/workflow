<?php

declare(strict_types=1);

namespace App\GraphQL\Directives;

class BroadcastNodeCreatedDirective extends BroadcastNodeEventDirective
{
    protected static function getNodeEvent(): string
    {
        return 'nodeCreated';
    }
}
