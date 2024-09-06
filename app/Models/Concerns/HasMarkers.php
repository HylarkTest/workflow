<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use Markers\Models\Concerns\HasMarkers as BaseHasMarkers;

trait HasMarkers
{
    use BaseHasMarkers, HasBaseScopedRelationships {
        HasBaseScopedRelationships::belongsToOne insteadof BaseHasMarkers;
        HasBaseScopedRelationships::morphToOne insteadof BaseHasMarkers;
    }
}
