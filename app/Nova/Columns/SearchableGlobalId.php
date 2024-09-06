<?php

declare(strict_types=1);

namespace App\Nova\Columns;

use LighthouseHelpers\Utils;
use Laravel\Nova\Query\Search\Column;
use Nuwave\Lighthouse\GlobalId\GlobalId;
use Nuwave\Lighthouse\GlobalId\GlobalIdException;

/**
 * @template TModel of \Illuminate\Database\Eloquent\Model
 */
class SearchableGlobalId extends Column
{
    public function __construct(string $column = 'id')
    {
        parent::__construct($column);
    }

    /**
     * Apply the search.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<TModel>  $query
     * @param  string  $search
     * @return \Illuminate\Database\Eloquent\Builder<TModel>
     */
    public function __invoke($query, $search, string $connectionType, string $whereOperator = 'orWhere')
    {
        try {
            [$type, $id] = resolve(GlobalId::class)->decode($search);
        } catch (GlobalIdException) {
            return $query;
        }
        $globalIdClass = Utils::namespaceModelClass($type);

        if (! $globalIdClass || $globalIdClass !== \get_class($query->getModel())) {
            return $query;
        }

        return $query->{$whereOperator}($this->column, $id);
    }
}
