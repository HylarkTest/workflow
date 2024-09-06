<?php

declare(strict_types=1);

namespace Markers\Core;

use Markers\Models\MarkerGroup;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Eloquent\Builder;

class MarkablePivotScope implements Scope
{
    /**
     * @param  \Illuminate\Database\Eloquent\Builder<\Illuminate\Database\Eloquent\Model>  $builder
     * @return void
     */
    public function apply(Builder $builder, Model $model) {}

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<\Illuminate\Database\Eloquent\Model>  $builder
     */
    public function extend(Builder $builder): void
    {
        $model = $this;
        $builder->macro('withPivotMarkers', function (Builder $builder, $relations = null) use ($model) {
            $relations = \is_array($relations) ? $relations : \array_slice(\func_get_args(), 1);

            return $builder->with($model->addPivotMarkersRelations($relations));
        });

        $builder->macro('withPivotMarker', function (Builder $builder, $relations = null) use ($model) {
            $relations = \is_array($relations) ? $relations : \array_slice(\func_get_args(), 1);

            return $builder->with($model->addPivotMarkersRelations($relations, 'pivotMarker'));
        });

        $builder->macro('withPivotMarkersFromGroup', function (Builder $builder, $group, $relations = null) use ($model) {
            $id = $group instanceof MarkerGroup ? $group->getKey() : $group;
            $relations = \is_array($relations) ? $relations : \array_slice(\func_get_args(), 2);

            return $builder->with($model->addPivotMarkersRelations(
                $relations, 'pivotMarkersFromGroup|'.$id
            ));
        });

        $builder->macro('withPivotMarkerFromGroup', function (Builder $builder, $group, $relations = null) use ($model) {
            $id = $group instanceof MarkerGroup ? $group->getKey() : $group;
            $relations = \is_array($relations) ? $relations : \array_slice(\func_get_args(), 2);

            return $builder->with($model->addPivotMarkersRelations(
                $relations, 'pivotMarkerFromGroup|'.$id
            ));
        });
    }

    /**
     * @param  string|string[]|null  $relations
     * @return array<int|string, string>|string
     */
    public function addPivotMarkersRelations(null|string|array $relations, string $pivotRelation = 'pivotMarkers'): array|string
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
}
