<?php

declare(strict_types=1);

namespace App\GraphQL\Directives;

class BroadcastNodeDeletedDirective extends BroadcastNodeEventDirective
{
    protected static function getNodeEvent(): string
    {
        return 'nodeDeleted';
    }
}
