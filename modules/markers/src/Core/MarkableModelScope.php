<?php

declare(strict_types=1);

namespace Markers\Core;

use Markers\Models\MarkerGroup;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Eloquent\Builder;

class MarkableModelScope implements Scope
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
        $builder->macro('withMarkersFromGroup', function (Builder $builder, $group) {
            $id = $group instanceof MarkerGroup ? $group->getKey() : $group;

            return $builder->with('markersFromGroup|'.$id);
        });

        $builder->macro('withMarkerFromGroup', function (Builder $builder, $group) {
            $id = $group instanceof MarkerGroup ? $group->getKey() : $group;

            return $builder->with('markerFromGroup|'.$id);
        });
    }
}
