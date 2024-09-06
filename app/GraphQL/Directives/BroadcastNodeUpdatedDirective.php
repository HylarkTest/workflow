<?php

declare(strict_types=1);

namespace App\GraphQL\Directives;

class BroadcastNodeUpdatedDirective extends BroadcastNodeEventDirective
{
    protected static function getNodeEvent(): string
    {
        return 'nodeUpdated';
    }
}
