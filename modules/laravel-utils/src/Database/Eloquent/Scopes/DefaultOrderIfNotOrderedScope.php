<?php

declare(strict_types=1);

namespace LaravelUtils\Database\Eloquent\Scopes;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Eloquent\Builder;

/**
 * @template TModel of \Illuminate\Database\Eloquent\Model
 */
class DefaultOrderIfNotOrderedScope implements Scope
{
    protected string $column;

    protected string $direction;

    public function __construct(string $column, string $direction = 'asc')
    {
        $this->column = $column;
        $this->direction = $direction;
    }

    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<TModel>  $builder
     * @param  TModel  $model
     */
    public function apply(Builder $builder, Model $model): void
    {
        if (empty($builder->getQuery()->orders)) {
            /** @phpstan-ignore-next-line Not sure why it cannot see the `orderBy` method */
            $builder->orderBy($this->column, $this->direction);
        }
    }
}
