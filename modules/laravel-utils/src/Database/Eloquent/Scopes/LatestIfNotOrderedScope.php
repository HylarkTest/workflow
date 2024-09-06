<?php

declare(strict_types=1);

namespace LaravelUtils\Database\Eloquent\Scopes;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Eloquent\Builder;

/**
 * @template TModel of \Illuminate\Database\Eloquent\Model
 */
class LatestIfNotOrderedScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<TModel>  $builder
     * @param  TModel  $model
     */
    public function apply(Builder $builder, Model $model): void
    {
        if (empty($builder->getQuery()->orders)) {
            $builder->latest()->orderBy('id');
        }
    }
}
