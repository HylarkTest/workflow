<?php

declare(strict_types=1);

namespace Markers\Models\Concerns;

use Markers\Models\Marker;
use Markers\Models\MarkerGroup;
use Markers\Models\MarkerPivot;
use Markers\Core\MarkableModelScope;
use Illuminate\Database\Eloquent\Collection;
use Markers\Models\Collections\MarkerCollection;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use LaravelUtils\Database\Eloquent\Relations\MorphToOne;
use LaravelUtils\Database\Eloquent\Concerns\HasAdvancedRelationships;

/**
 * Trait HasMarkers
 *
 * @property \Markers\Models\Collections\MarkerCollection $markers
 * @property \Markers\Models\Marker $marker
 *
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait HasMarkers
{
    use HasAdvancedRelationships;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany<\Markers\Models\Marker>
     */
    public function markers(): MorphToMany
    {
        /** @var class-string<\Markers\Models\Marker> $markerClass */
        $markerClass = config('markers.models.marker');
        /** @var \Illuminate\Database\Eloquent\Relations\MorphToMany<\Markers\Models\Marker> $query */
        $query = $this->morphToMany($markerClass, 'markable')
            ->using(MarkerPivot::class)
            ->withPivot('created_at as added_at')
            ->withTimestamps();

        return $query;
    }

    public function getMarkers(): MarkerCollection
    {
        return $this->markers;
    }

    /**
     * @return \LaravelUtils\Database\Eloquent\Relations\MorphToOne<\Markers\Models\Marker>
     */
    public function marker(): MorphToOne
    {
        return $this->morphToOne(Marker::class, 'markable')
            ->withPivot('created_at as added_at')
            ->withTimestamps();
    }

    public function getMarker(): Marker
    {
        return $this->marker;
    }

    public function loadMarkersFromGroup(int|MarkerGroup $group): static
    {
        $id = $group instanceof MarkerGroup ? $group->getKey() : $group;

        return $this->load('markersFromGroup|'.$id);
    }

    public function loadMarkerFromGroup(int|MarkerGroup $group): static
    {
        $id = $group instanceof MarkerGroup ? $group->getKey() : $group;

        return $this->load('markerFromGroup|'.$id);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany<\Markers\Models\Marker>
     */
    public function markersFromGroup(int|MarkerGroup $group): MorphToMany
    {
        $id = $group instanceof MarkerGroup ? $group->getKey() : $group;

        return $this->markers()->where('marker_group_id', $id);
    }

    /**
     * @return \LaravelUtils\Database\Eloquent\Relations\MorphToOne<\Markers\Models\Marker>
     */
    public function markerFromGroup(int|MarkerGroup $group): MorphToOne
    {
        $id = $group instanceof MarkerGroup ? $group->getKey() : $group;

        return $this->marker()->where('marker_group_id', $id);
    }

    public function getMarkersFromGroup(int|MarkerGroup $group): MarkerCollection
    {
        $id = $group instanceof MarkerGroup ? $group->getKey() : $group;
        $relationName = 'markersFromGroup|'.$id;

        return $this->$relationName;
    }

    public function getMarkerFromGroup(int|MarkerGroup $group): Marker
    {
        $id = $group instanceof MarkerGroup ? $group->getKey() : $group;
        $relationName = 'markerFromGroup|'.$id;

        return $this->$relationName;
    }

    /**
     * @param  string  $method
     * @param  array<int, mixed>  $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        if (preg_match('/^markers?FromGroup\|/', $method)) {
            [$relationName, $groupId] = explode('|', $method, 2);

            return $this->$relationName($groupId);
        }

        return parent::__call($method, $parameters);
    }

    public static function bootHasMarkers(): void
    {
        (new self)->registerMarkerRelationMacros();
        static::addGlobalScope(new MarkableModelScope);
    }

    protected function registerMarkerRelationMacros(): void
    {
        Collection::macro('loadMarkersFromGroup', function (MarkerGroup $group) {
            return $this->load('markersFromGroup|'.$group->getKey());
        });
    }
}
