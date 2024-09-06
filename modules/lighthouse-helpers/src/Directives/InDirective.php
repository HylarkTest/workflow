<?php

declare(strict_types=1);

namespace LighthouseHelpers\Directives;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Nuwave\Lighthouse\Schema\Directives\InDirective as BaseInDirective;

/**
 * Class InDirective
 *
 * The Lighthouse @in directive fails when a null value is passed in.
 * The expected behaviour is that it ignores that filter, rather than returning
 * nothing.
 */
class InDirective extends BaseInDirective
{
    /**
     * Apply a simple "WHERE IN $values" clause.
     *
     * @template TModel of \Illuminate\Database\Eloquent\Model
     *
     * @param  \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder<TModel>|\Illuminate\Database\Eloquent\Relations\Relation<TModel>  $builder
     * @param  mixed  $value
     * @return \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder<TModel>|\Illuminate\Database\Eloquent\Relations\Relation<TModel>
     */
    public function handleBuilder(QueryBuilder|EloquentBuilder|Relation $builder, $value): QueryBuilder|EloquentBuilder|Relation
    {
        if ($value !== null) {
            /** @var \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder<TModel>|\Illuminate\Database\Eloquent\Relations\Relation<TModel> $builder */
            $builder = parent::handleBuilder($builder, $value);

            return $builder;
        }

        return $builder;
    }
}
