<?php

declare(strict_types=1);

namespace Markers\Models\Concerns;

use Markers\Models\Marker;
use Markers\Models\MarkerGroup;
use Markers\Core\MarkablePivotScope;
use Markers\Core\PivotMarkersRelation;
use Illuminate\Database\Eloquent\Collection;
use Markers\Models\Collections\MarkerCollection;

/**
 * Trait HasRelationshipMarkers
 *
 * @property \Markers\Models\Collections\MarkerCollection $pivotMarkers
 * @property \Markers\Models\Marker $pivotMarker
 *
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait HasPivotMarkers
{
    /**
     * @return \Markers\Core\PivotMarkersRelation<\Markers\Models\Marker>
     */
    public function pivotMarkers(string $relationName = 'markers'): PivotMarkersRelation
    {
        /** @var \Markers\Models\Marker $instance */
        $instance = $this->newRelatedInstance(Marker::class);

        return new PivotMarkersRelation($this, $instance, $relationName);
    }

    /**
     * @return \Markers\Core\PivotMarkersRelation<\Markers\Models\Marker>
     */
    public function pivotMarker(string $relationName = 'marker'): PivotMarkersRelation
    {
        $instance = $this->newRelatedInstance(Marker::class);

        return new PivotMarkersRelation($this, $instance, $relationName, true);
    }

    /**
     * @return \Markers\Core\PivotMarkersRelation<\Markers\Models\Marker>
     */
    public function pivotMarkersFromGroup(int|MarkerGroup $group): PivotMarkersRelation
    {
        $id = $group instanceof MarkerGroup ? $group->getKey() : $group;

        return $this->pivotMarkers('markersFromGroup|'.$id)->where('marker_group_id', $id);
    }

    /**
     * @return \Markers\Core\PivotMarkersRelation<\Markers\Models\Marker>
     */
    public function pivotMarkerFromGroup(int|MarkerGroup $group): PivotMarkersRelation
    {
        $id = $group instanceof MarkerGroup ? $group->getKey() : $group;

        return $this->pivotMarker('markerFromGroup|'.$id)->where('marker_group_id', $id);
    }

    public function getPivotMarkers(): MarkerCollection
    {
        return $this->pivotMarkers;
    }

    public function getPivotMarker(): Marker
    {
        return $this->pivotMarker;
    }

    public function getPivotMarkersFromGroup(int|MarkerGroup $group): MarkerCollection
    {
        $id = $group instanceof MarkerGroup ? $group->getKey() : $group;
        $relationName = 'pivotMarkersFromGroup|'.$id;

        return $this->$relationName;
    }

    public function getPivotMarkerFromGroup(int|MarkerGroup $group): MarkerCollection
    {
        $id = $group instanceof MarkerGroup ? $group->getKey() : $group;
        $relationName = 'pivotMarkerFromGroup|'.$id;

        return $this->$relationName;
    }

    public function loadWithMarkers(mixed $relations = null): self
    {
        $relations = \is_array($relations) ? $relations : \func_get_args();

        return $this->load($this->addPivotMarkersRelations($relations));
    }

    public function loadWithMarker(mixed $relations = null): self
    {
        $relations = \is_array($relations) ? $relations : \func_get_args();

        return $this->load($this->addPivotMarkersRelations($relations, 'pivotMarker'));
    }

    public function loadWithMarkersFromGroup(int|MarkerGroup $group, mixed $relations = null): self
    {
        $id = $group instanceof MarkerGroup ? $group->getKey() : $group;
        $relations = \is_array($relations) ? $relations : \array_slice(\func_get_args(), 1);

        return $this->load($this->addPivotMarkersRelations(
            $relations, 'pivotMarkersFromGroup|'.$id
        ));
    }

    public function loadWithMarkerFromGroup(int|MarkerGroup $group, mixed $relations = null): self
    {
        $id = $group instanceof MarkerGroup ? $group->getKey() : $group;
        $relations = \is_array($relations) ? $relations : \array_slice(\func_get_args(), 1);

        return $this->load($this->addPivotMarkersRelations(
            $relations, 'pivotMarkersFromGroup|'.$id
        ));
    }

    /**
     * @param  string  $method
     * @param  array<int, mixed>  $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        if (preg_match('/^pivotMarkers?FromGroup\|/', $method)) {
            [$relationName, $groupId] = explode('|', $method, 2);

            return $this->$relationName($groupId);
        }

        return parent::__call($method, $parameters);
    }

    public static function bootHasPivotMarkers(): void
    {
        (new self)->registerRelationshipMarkerRelationMacros();
        static::addGlobalScope(new MarkablePivotScope);
    }

    /**
     * @param  string|string[]|null  $relations
     * @return string|array<string>
     */
    public function addPivotMarkersRelations(array|string|null $relations, string $pivotRelation = 'pivotMarkers'): array|string
    {
        if (! $relations) {
            return $pivotRelation;
        }

        return collect((array) $relations)->mapWithKeys(static function ($relation, $key) use ($pivotRelation) {
            if (\is_string($key)) {
                $key .= '.'.$pivotRelation;
            } else {
                $relation .= '.'.$pivotRelation;
            }

            return [$key => $relation];
        })->all();
    }

    protected function registerRelationshipMarkerRelationMacros(): void
    {
        $model = $this;
        Collection::macro('loadWithPivotMarkers', function ($relations = null) use ($model) {
            return $this->load($model->addPivotMarkersRelations($relations));
        });

        Collection::macro('loadWithPivotMarker', function ($relations = null) use ($model) {
            return $this->load($model->addPivotMarkersRelations($relations, 'pivotMarker'));
        });

        Collection::macro('loadWithPivotMarkersFromGroup', function (MarkerGroup $group, $relations = null) use ($model) {
            return $this->load($model->addPivotMarkersRelations(
                $relations, 'pivotMarkersFromGroup|'.$group->getKey(),
            ));
        });

        Collection::macro('loadWithPivotMarkerFromGroup', function (MarkerGroup $group, $relations = null) use ($model) {
            return $this->load($model->addPivotMarkersRelations(
                $relations, 'pivotMarkerFromGroup|'.$group->getKey(),
            ));
        });
    }
}
