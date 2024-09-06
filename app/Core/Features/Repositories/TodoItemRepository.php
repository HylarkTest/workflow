<?php

declare(strict_types=1);

namespace App\Core\Features\Repositories;

use App\Models\Base;
use App\Models\Item;
use App\Models\Space;
use App\Models\Mapping;
use Lampager\Paginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use GraphQL\Executor\Promise\Adapter\SyncPromise;
use App\Core\Mappings\Features\MappingFeatureType;

/**
 * @extends FeatureItemRepository<\App\Models\Todo, \App\Models\TodoList>
 *
 * @method \Illuminate\Database\Eloquent\Builder<\App\Models\Todo> getRawBuilder(Base $base, ?array $listIds = [], Item|string|null $node = null, int|Mapping|null $mapping = null, Space|int|null $space = null)
 */
class TodoItemRepository extends FeatureItemRepository
{
    /**
     * @param  PaginationArgs  $paginationArgs
     */
    public function paginateFeatureItems(
        Base $base,
        array $paginationArgs,
        ?array $listIds = [],
        Item|string|null $node = null,
        int|Mapping|null $mapping = null,
        Space|int|null $space = null,
        array $filters = [],
        array $orderBy = [],
        ?string $group = null,
    ): array|SyncPromise {
        $query = $this->getRawBuilder($base, $listIds, $node, $mapping, $space);

        $rawQuery = clone $query->toBase();

        $this->applyFiltersToQuery($query, Arr::except($filters, ['filters']));

        $prefilterQuery = clone $query;
        $meta = [
            'incompleteCount' => static fn () => (clone $prefilterQuery)->incomplete()->count(),
            'completedCount' => static fn () => (clone $prefilterQuery)->completed()->count(),
        ];

        $this->applyFiltersToQuery($query, Arr::only($filters, ['filters']));

        if ($orderBy && \in_array($this->getListOrderByField(), Arr::pluck($orderBy, 'field'), true)) {
            $model = $query->getModel();
            $table = $model->getTable();
            $listModel = $model->list()->getModel();
            $query->addSelect($listModel->getTable().'.order as list_order');
            $query->join(
                $listModel->getTable(),
                function (JoinClause $clause) use ($listModel, $table) {
                    $clause->on($listModel->getQualifiedKeyName(), '=', $table.'.'.$listModel->getForeignKey())
                        ->on($listModel->qualifyColumn('base_id'), '=', "$table.base_id");
                }
            );
        }

        if ($group) {
            $this->base = $base;
            $groups = $this->buildGroupedPaginator($group, $query, $paginationArgs, $rawQuery, $orderBy, $filters);
            $groups['meta'] = $meta;

            return $groups;
        }

        $deferred = $this->buildPaginator($query, $paginationArgs, $rawQuery, $orderBy);

        return $deferred->then(function ($paginator) use ($meta) {
            $paginator->meta = $meta;

            return $paginator;
        });
    }

    public function getFeatureStats(Base $base, ?array $listIds = null, Item|string|null $node = null, int|Mapping|null $mapping = null): array
    {
        $query = $this->getRawBuilder($base, $listIds, $node, $mapping);

        $query->incomplete();

        return [
            'todayCount' => fn () => (clone $query)->whereDay('due_by', '=', today())->count(),
            'highPriorityCount' => fn () => (clone $query)->where('priority', 1)->count(),
            'totalCount' => fn () => (clone $query)->count(),
            'scheduledCount' => fn () => (clone $query)->whereNotNull('due_by')->count(),
            'overdueCount' => fn () => (clone $query)->where('due_by', '<', now())->count(),
        ];
    }

    protected function applyColumnOrderBy(Paginator $lampager, string $column, string $direction): void
    {
        $builder = $lampager->builder;
        if ($column === 'is_completed') {
            $builder->addSelect(DB::raw('CASE WHEN completed_at IS NOT NULL THEN 1 ELSE 0 END AS is_completed'));
            $lampager->orderBy('is_completed', $direction);
        } elseif ($column === 'completed_at') {
            $builder->addSelect(DB::raw('CASE WHEN completed_at IS NULL THEN \'9999-01-01 00:00:00\' ELSE completed_at END as formatted_completed_at'));
            $lampager->orderBy('formatted_completed_at', $direction);
        } else {
            parent::applyColumnOrderBy($lampager, $column, $direction);
        }
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<\App\Models\Todo>  $query
     * @return \Illuminate\Database\Eloquent\Builder<\App\Models\Todo>
     */
    protected function applyFilters(Builder $query, array $filters): Builder
    {
        if (isset($filters['dueBefore'])) {
            $query->dueBefore($filters['dueBefore']);
        }

        if (isset($filters['isCompleted'])) {
            $method = $filters['isCompleted'] ? 'completed' : 'incomplete';
            $query->$method();
        }

        if (isset($filters['dueAfter'])) {
            $query->dueAfter($filters['dueAfter']);
        }

        if (isset($filters['isScheduled'])) {
            $query->whereNotNull('due_by');
        }

        if (isset($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        } else {
            if (isset($filters['maxPriority'])) {
                $query->where('priority', '<=', $filters['maxPriority'])
                    ->where('priority', '<>', 0);
            }

            if (isset($filters['minPriority'])) {
                $query->where('priority', '>=', $filters['minPriority'])
                    ->where('priority', '<>', 0);
            }
        }

        return $query;
    }

    protected function getListOrderByField(): string
    {
        return 'todoList';
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder<\App\Models\Todo>
     */
    protected function getItemQuery(Base $base): Builder
    {
        return $base->todos()->getQuery();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder<\App\Models\TodoList>
     */
    protected function getListQuery(Base $base): Builder
    {
        return $base->todoLists()->getQuery();
    }

    protected function getFeatureType(): MappingFeatureType
    {
        return MappingFeatureType::TODOS;
    }
}
