<?php

declare(strict_types=1);

namespace LighthouseHelpers\Directives;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Nuwave\Lighthouse\OrderBy\OrderByDirective as BaseOrderByDirective;

class SafeOrderByDirective extends BaseOrderByDirective
{
    public static function definition(): string
    {
        return /* @lang GraphQL */ <<<'SDL'
"""
Sort a result list by one or more given columns.
"""
directive @safeOrderBy(
    """
    Restrict the allowed column names to a well-defined list.
    This improves introspection capabilities and security.
    Mutually exclusive with the `columnsEnum` argument.
    Only used when the directive is added on an argument.
    """
    columns: [String!]

    """
    Use an existing enumeration type to restrict the allowed columns to a predefined list.
    This allowes you to re-use the same enum for multiple fields.
    Mutually exclusive with the `columns` argument.
    Only used when the directive is added on an argument.
    """
    columnsEnum: String

    """
    The database column for which the order by clause will be applied.
    Only used when the directive is added on a field.
    """
    column: String

    """
    The direction of the order by clause.
    Only used when the directive is added on a field.
    """
    direction: OrderByDirection = ASC
) on ARGUMENT_DEFINITION
SDL;
    }

    /**
     * Apply an "ORDER BY" clause.
     *
     * @param  \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder<\Illuminate\Database\Eloquent\Model>  $builder
     * @param  mixed  $value
     * @return \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder<\Illuminate\Database\Eloquent\Model>
     *
     * @phpstan-ignore-next-line
     */
    public function handleBuilder(QueryBuilder|EloquentBuilder|Relation $builder, $value): QueryBuilder|EloquentBuilder|Relation
    {
        $table = $this->directiveArgValue('table');

        if (! $table && $builder instanceof Builder) {
            /** @var \Illuminate\Database\Eloquent\Model $model */
            $model = $builder->getModel();
            $table = $model->getTable();
        }

        if ($table) {
            $value = array_map(function ($orderByClause) use ($table) {
                if (isset($orderByClause['field'])) {
                    $orderByClause['column'] = $orderByClause['field'];
                }
                if (isset($orderByClause['direction'])) {
                    $orderByClause['order'] = $orderByClause['direction'];
                }

                if (! Str::startsWith($orderByClause['column'], $table.'.')) {
                    $orderByClause['column'] = $table.'.'.$orderByClause['column'];
                }

                return $orderByClause;
            }, $value);
        }

        return parent::handleBuilder($builder, $value);
    }
}
