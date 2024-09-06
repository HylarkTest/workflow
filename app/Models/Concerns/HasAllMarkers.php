<?php

declare(strict_types=1);

namespace App\Models\Concerns;

trait HasAllMarkers
{
    use HasMarkers {
        HasMarkers::__call as markersCall;
    }
    use HasPivotMarkers {
        HasPivotMarkers::__call as pivotMarkersCall;
    }

    /**
     * @param  string  $method
     * @param  array  $parameters
     */
    public function __call($method, $parameters)
    {
        if (preg_match('/^(pivotM|m)arkers?FromGroup\|/', $method)) {
            [$relationName, $groupId] = explode('|', $method, 2);

            return $this->$relationName((int) $groupId);
        }

        return parent::__call($method, $parameters);
    }
}
