<?php

declare(strict_types=1);

namespace LighthouseHelpers\Directives;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Nuwave\Lighthouse\Schema\Directives\BaseDirective;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Nuwave\Lighthouse\Support\Contracts\ArgBuilderDirective;

class HasDirective extends BaseDirective implements ArgBuilderDirective
{
    public static function definition(): string
    {
        return /* @lang GraphQL */ <<<'SDL'
"""
Query if the model has a relation
"""
directive @has(
  """
  Specify the name of the relation to check
  """
  relation: String!
) on ARGUMENT_DEFINITION
SDL;
    }

    /**
     * Add additional constraints to the builder based on the given argument value.
     *
     * @template TModel of \Illuminate\Database\Eloquent\Model
     *
     * @param  \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder<TModel>|\Illuminate\Database\Eloquent\Relations\Relation<TModel>  $builder
     * @return \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder<TModel>|\Illuminate\Database\Eloquent\Relations\Relation<TModel>
     */
    public function handleBuilder(QueryBuilder|EloquentBuilder|Relation $builder, mixed $value): QueryBuilder|EloquentBuilder|Relation
    {
        if (! filled($value)) {
            return $builder;
        }

        if ($builder instanceof Relation) {
            $builder->getQuery()->has($this->directiveArgValue('relation'), $value ? '>=' : '<');
        } elseif ($builder instanceof EloquentBuilder) {
            $builder->has($this->directiveArgValue('relation'), $value ? '>=' : '<');
        }

        return $builder;
    }
}
