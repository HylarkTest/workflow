<?php

declare(strict_types=1);

namespace Markers\Models;

use Markers\Models\Collections\MarkerCollection;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use LaravelUtils\Database\Eloquent\Relations\MorphToOne;

/**
 * @property \Illuminate\Database\Eloquent\Collection<int, \Markers\Models\Marker> $markers
 */
interface MarkableModel
{
    /**
     * @return int
     */
    public function getKey();

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany<\Markers\Models\Marker>
     */
    public function markers(): MorphToMany;

    public function getMarkers(): MarkerCollection;

    /**
     * @return \LaravelUtils\Database\Eloquent\Relations\MorphToOne<\Markers\Models\Marker>
     */
    public function marker(): MorphToOne;

    public function getMarker(): Marker;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany<\Markers\Models\Marker>
     */
    public function markersFromGroup(MarkerGroup $group): MorphToMany;

    public function getMarkersFromGroup(MarkerGroup $group): MarkerCollection;
}
