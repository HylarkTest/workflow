<?php

declare(strict_types=1);

namespace LighthouseHelpers\Pagination;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Nuwave\Lighthouse\Execution\ModelsLoader\CountModelsLoader;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Nuwave\Lighthouse\Execution\ModelsLoader\PaginatedModelsLoader as BasePaginatedModelsLoader;

/**
 * @property \LighthouseHelpers\Pagination\PaginationArgs $paginationArgs
 */
class PaginatedModelsLoader extends BasePaginatedModelsLoader
{
    /**
     * @var \Lampager\Laravel\Paginator[]
     */
    protected array $cursorQueries;

    /**
     * @param  \Illuminate\Database\Eloquent\Collection<int, \Illuminate\Database\Eloquent\Model>  $parents
     * @return \Illuminate\Database\Eloquent\Collection<int, \Illuminate\Database\Eloquent\Model>
     */
    protected function loadRelatedModels(EloquentCollection $parents): EloquentCollection
    {
        if ($this->paginationArgs->type->isPaginator()) {
            return parent::loadRelatedModels($parents);
        }

        /** @var \Illuminate\Support\Collection<int, \Illuminate\Database\Eloquent\Relations\Relation<\Illuminate\Database\Eloquent\Model>> $relations */
        $relations = $parents
            ->toBase()
            ->map(function (Model $model, $index) use ($parents): Relation {
                $relation = $this->relationInstance($parents);

                $relation->addEagerConstraints([$model]);

                ($this->decorateBuilder)($relation, $model);

                if ($relation instanceof BelongsToMany || $relation instanceof HasManyThrough) {
                    $shouldSelect = new \ReflectionMethod($relation::class, 'shouldSelect');
                    $shouldSelect->setAccessible(true);
                    $select = $shouldSelect->invoke($relation, ['*']);

                    $relation->addSelect($select);
                }

                $relation->initRelation([$model], $this->relation);

                /** @var array|int $page */
                $page = $this->paginationArgs->cursor;

                /** @var \Lampager\Laravel\Paginator $query */
                $query = $relation->lampager();

                $query = $query->useProcessor(CursorProcessor::class)
                    ->forward()
                    ->limit($this->paginationArgs->first)
                    ->seekable()
                    ->exclusive();

                $hasIdOrder = false;
                $baseQuery = $relation->toBase();
                if ($orders = $baseQuery->orders) {
                    foreach ($orders as $order) {
                        $query->orderBy($order['column'], $order['direction']);
                        if ($order['column'] === 'id') {
                            $hasIdOrder = true;
                        }
                    }
                    $baseQuery->orders = [];
                }
                if (! $hasIdOrder) {
                    $query->orderBy('id');
                }

                /*
                 * We need this cursor query for building the cursors
                 * later on.
                 */
                $this->cursorQueries[$index] = $query;

                /** @var \Illuminate\Database\Eloquent\Relations\Relation<\Illuminate\Database\Eloquent\Model> $relation */
                $relation = $query->transform($query->configure(\is_int($page) ? [] : $page));

                return $relation;
            });

        // Merge all the relation queries into a single query with UNION ALL.

        /**
         * Use the first query as the initial starting point.
         *
         * We can assume this to be non-null because only non-empty lists of parents
         * are passed into this loader.
         *
         * @var \Illuminate\Database\Eloquent\Relations\Relation<\Illuminate\Database\Eloquent\Model> $firstRelation
         */
        $firstRelation = $relations->shift();

        // We have to make sure to use ->getQuery() in order to respect
        // model scopes, such as soft deletes
        $mergedRelationQuery = $relations->reduce(
            static function (EloquentBuilder $builder, Relation $relation): EloquentBuilder {
                return $builder->unionAll(
                    $relation->toBase()
                );
            },
            $firstRelation->getQuery()
        );

        return $mergedRelationQuery->get();
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Collection<int, \Illuminate\Database\Eloquent\Model>  $parents
     */
    protected function convertRelationToPaginator(EloquentCollection $parents): void
    {
        if ($this->paginationArgs->type->isPaginator()) {
            parent::convertRelationToPaginator($parents);

            return;
        }

        /** @var int|array $page */
        $page = $this->paginationArgs->cursor;

        $parents->each(function (Model $model, $index) use ($page): void {
            $total = CountModelsLoader::extractCount($model, $this->relation);

            $query = $this->cursorQueries[$index];
            $paginator = $query->process($query->configure(\is_int($page) ? [] : $page), $model->getRelation($this->relation));
            $paginator->total = $total;

            $model->setRelation($this->relation, $paginator);
        });
    }
}
