<?php

declare(strict_types=1);

namespace CitusLaravel\Database\Eloquent;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @template TRelatedModel of \Illuminate\Database\Eloquent\Model
 * @template TChildModel of \Illuminate\Database\Eloquent\Model
 *
 * @extends \Illuminate\Database\Eloquent\Relations\BelongsTo<TRelatedModel, TChildModel>
 */
class DistributedBelongsTo extends BelongsTo
{
    /**
     * @param  \Illuminate\Database\Eloquent\Builder<TChildModel>  $query
     * @param  \Illuminate\Database\Eloquent\Builder<TRelatedModel>  $parentQuery
     * @param  string[]  $columns
     * @return \Illuminate\Database\Eloquent\Builder<TRelatedModel>
     */
    public function getRelationExistenceQuery(Builder $query, Builder $parentQuery, $columns = ['*'])
    {
        $query = parent::getRelationExistenceQuery($query, $parentQuery, $columns);

        if (\in_array(DistributedModel::class, class_uses_recursive($this->parent), true)) {
            /** @phpstan-ignore-next-line We know these methods exist */
            $query->whereColumn($this->child->getQualifiedDistributedColumn(), $this->parent->getQualifiedDistributedColumn());
        }

        return $query;
    }
}
