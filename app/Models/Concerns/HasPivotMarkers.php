<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use Markers\Core\PivotMarkersRelation;
use Markers\Models\Concerns\HasPivotMarkers as BaseHasPivotMarkers;

trait HasPivotMarkers
{
    use BaseHasPivotMarkers {
        pivotMarkers as basePivotMarkers;
        pivotMarker as basePivotMarker;
    }
    use HasBaseScopedRelationships;

    /**
     * @return \Markers\Core\PivotMarkersRelation<\Markers\Models\Marker>
     */
    public function pivotMarkers(string $relationName = 'markers'): PivotMarkersRelation
    {
        return $this->addDistributedPivotValue(
            $this->basePivotMarkers($relationName)
                ->withTimestamps()
        );
    }

    /**
     * @return \Markers\Core\PivotMarkersRelation<\Markers\Models\Marker>
     */
    public function pivotMarker(string $relationName = 'markers'): PivotMarkersRelation
    {
        return $this->addDistributedPivotValue(
            $this->basePivotMarker($relationName)
                ->withTimestamps()
        );
    }
}
