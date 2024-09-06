<?php

declare(strict_types=1);

namespace App\Core\Features\Repositories;

use App\Models\Base;
use App\Models\Item;
use App\Models\Space;
use App\Models\Mapping;
use Lampager\Paginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use LighthouseHelpers\Utils;
use Illuminate\Support\Collection;
use App\Models\Contracts\FeatureList;
use Nuwave\Lighthouse\GlobalId\GlobalId;
use App\Models\Contracts\FeatureListItem;
use Illuminate\Database\Query\JoinClause;
use App\Core\Mappings\MarkerFilterOperator;
use App\GraphQL\Queries\Concerns\GroupsQueries;
use Illuminate\Contracts\Database\Query\Builder;
use GraphQL\Executor\Promise\Adapter\SyncPromise;
use App\Core\Mappings\Features\MappingFeatureType;
use App\GraphQL\Queries\Concerns\PaginatesQueries;
use LaravelUtils\Database\Eloquent\Contracts\Sortable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;

/**
 * @template TItem of \App\Models\Contracts\FeatureListItem
 * @template TList of \App\Models\Contracts\FeatureList
 */
abstract class FeatureItemRepository
{
    /** @use \App\GraphQL\Queries\Concerns\GroupsQueries<TItem> */
    /** @phpstan-ignore-next-line Yes it is */
    use GroupsQueries;

    use PaginatesQueries;

    protected ?Base $base = null;

    public function __construct(protected GlobalId $globalId) {}

    /**
     * @param  PaginationArgs  $paginationArgs
     * @param  array<int, int>  $listIds
     * @param array{
     *     search?: string,
     *     filter?: string,
     *     markers?: string[],
     *     includeGroups?: string[],
     *     excludeGroups?: string[],
     * } $filters
     * @param  OrderBy  $orderBy
     */
    public function paginateFeatureItems(
        Base $base,
        array $paginationArgs,
        ?array $listIds = [],
        Item|string|null $node = null,
        Mapping|int|null $mapping = null,
        Space|int|null $space = null,
        array $filters = [],
        array $orderBy = [],
        ?string $group = null,
    ): array|SyncPromise {
        if (! $space && ($node || $mapping)) {
            if ($mapping) {
                if (\is_int($mapping)) {
                    $mapping = $base->mappings()->findOrFail($mapping);
                }
                $space = $mapping->space;
            } else {
                if ($node) {
                    if (\is_string($node)) {
                        /** @var \App\Models\Item $node */
                        $node = Utils::resolveModelFromGlobalId($node);
                    }
                    $space = $node->mapping->space;
                }
            }
        }

        $query = $this->getRawBuilder($base, $listIds, $node, $mapping, $space);

        $rawQuery = clone $query->toBase();

        $this->applyFiltersToQuery($query, $filters);

        if ($orderBy && \in_array($this->getListOrderByField(), Arr::pluck($orderBy, 'field'), true)) {
            $model = $query->getModel();
            $table = $model->getTable();
            /** @phpstan-ignore-next-line */
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

            return $this->buildGroupedPaginator(
                $group,
                $query,
                $paginationArgs,
                $rawQuery,
                $orderBy,
                [
                    ...$filters,
                    'space' => $space,
                ]
            );
        }

        return $this->buildPaginator($query, $paginationArgs, $rawQuery, $orderBy);
    }

    /**
     * @param  array<int, int>|null  $listIds
     * @return \Closure[]
     */
    public function getFeatureStats(
        Base $base,
        ?array $listIds = null,
        Item|string|null $node = null,
        Mapping|int|null $mapping = null,
    ) {
        $query = $this->getRawBuilder($base, $listIds, $node, $mapping);

        return [
            'favoritesCount' => fn () => $this->applyFilters(clone $query, ['isFavorited' => true])->count(),
            'totalCount' => fn () => (clone $query)->count(),
        ];
    }

    /**
     * @return TItem
     */
    public function getFeatureItem(Base $base, string|int $id, bool $withTrashed = false): FeatureListItem
    {
        if (! is_numeric($id)) {
            $id = $this->globalId->decodeID($id);
        }
        $query = $this->getItemQuery($base);
        if ($withTrashed) {
            /** @phpstan-ignore-next-line Spent too much time on this */
            $query->withTrashed();
        }
        /** @var TItem $item */
        $item = $query->findOrFail((int) $id);

        return $item;
    }

    /**
     * @param array{
     *     markers?: array<array{
     *         groupId: int,
     *         markers: int[],
     *     }>,
     *     associations?: int[],
     *     assigneeGroups?: array<array{
     *         groupId: int,
     *         assignees: int[],
     *     }>,
     * } $data
     * @return TItem
     */
    public function createFeatureItem(
        Base $base,
        array $data,
    ): FeatureListItem {
        $assignees = $this->getAssigneesFromInput($base, $data);

        /** @phpstan-ignore-next-line Not sure how to add this dynamic key to the array shape */
        $listId = $data[$this->getListKey($base)];

        /** @var TList $list */
        $list = $this->getListQuery($base)->findOrFail($listId);

        /** @phpstan-ignore-next-line Ugh TList has space!! */
        $markers = $this->getMarkersFromInput($base, $list->space, $data, $this->getFeatureType());

        /** @phpstan-ignore-next-line The property exists in the interface */
        $associatedItems = $this->getAssociatedItems($base, $data, $list->space, $this->getFeatureType());

        /** @var TItem $listItem */
        $listItem = $this->createFeatureItemFromAttributes($list, $data);

        foreach ($markers as $marker) {
            $listItem->markers()->attach($marker);
        }

        foreach ($associatedItems as $associatedItem) {
            $listItem->items()->attach($associatedItem);
        }

        foreach ($assignees as $assigneeInfo) {
            $listItem->assignees()->attach($assigneeInfo['assignees'], ['group_id' => $assigneeInfo['group']->id]);
        }

        return $listItem;
    }

    /**
     * @return TItem
     */
    public function updateFeatureItem(
        Base $base,
        int|string $id,
        array $data
    ): FeatureListItem {
        $listId = $data[$this->getListKey($base)] ?? null;

        $item = $this->getFeatureItem($base, $id);

        /** @phpstan-ignore-next-line The property exists in the interface */
        $originalList = $item->list;

        /** @var TList|null $list */
        $list = $listId ? $this->getListQuery($base)->where('space_id', $originalList->space_id)->findOrFail($listId) : null;

        if ($list && $list->isNot($originalList)) {
            $item->list()->associate($list);
        }

        if (isset($data['isFavorite'])) {
            $data['favorited_at'] = $data['isFavorite'] ? now() : null;
            unset($data['isFavorite']);
        }

        $this->updateFeatureItemFromAttributes($item, Arr::except($data, $this->getListKey($base)));
        $item->save();

        return $item;
    }

    /**
     * @return TItem
     */
    public function duplicateFeatureItem(
        Base $base,
        int|string $id,
        array $data = []
    ): FeatureListItem {
        $item = $this->getFeatureItem($base, $id);

        $itemDuplicated = $this->duplicateFeatureItemFromAttributes($item, []);

        if ($data['withMarkers'] ?? null) {
            $itemDuplicated->markers()->attach($item->getMarkers()->modelKeys());
        }

        if ($data['withAssociations'] ?? null) {
            $itemDuplicated->items()->attach($item->items()->get()->pluck('id'));
        }

        if ($data['withAssignees'] ?? null) {
            foreach ($this->getAssigneesGrouped($item) as $assigneeInfo) {
                $itemDuplicated->assignees()->attach($assigneeInfo['assignees'], ['group_id' => $assigneeInfo['groupId']]);
            }
        }

        return $itemDuplicated;
    }

    public function deleteFeatureItem(Base $base, int|string $id, array $args = []): bool
    {
        $force = $args['force'] ?? false;
        /** @var TItem $item */
        $item = $this->getFeatureItem($base, $id, $force);

        if ($force) {
            return (bool) $item->forceDelete();
        }

        return (bool) $item->delete();
    }

    /**
     * @return TItem|null
     */
    public function restoreFeatureItem(Base $base, int $id): ?FeatureListItem
    {
        $item = $this->getFeatureItem($base, $id, true);

        if ($item->trashed()) {
            $success = $item->restore();

            return $success ? $item : null;
        }

        return null;
    }

    /**
     * @param  TItem  $item
     */
    protected function getAssigneesGrouped(FeatureListItem $item): array
    {
        $itemAssignees = $item->assignees()
            ->get(['group_id', 'member_id'])
            ->groupBy('group_id')
            ->map(function ($assignee) {
                return $assignee->pluck('member_id')->toArray();
            });

        $data = [];
        foreach ($itemAssignees as $group_id => $assignees) {
            $data[] = [
                'groupId' => $group_id,
                'assignees' => $assignees,
            ];
        }

        return $data;
    }

    /**
     * @return \Illuminate\Support\Collection<int, TList>
     */
    protected function getListHeaders(?string $id, ?array $filters = []): Collection
    {
        /** @phpstan-ignore-next-line $base is not null here */
        return $this->getListQuery($this->base)
            ->when(
                $filters['space'] ?? null,
                fn (EloquentBuilder $query, int|Space $space) => $query->where('space_id', \is_int($space) ? $space : $space->getKey()),
                fn (EloquentBuilder $query) => $query->orderBy('space_id')
            )
            ->orderBy('order')
            ->get();
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<TItem>  $query
     * @return \Illuminate\Database\Eloquent\Builder<TItem>
     *
     * @phpstan-ignore-next-line Not sure how to resolve this
     */
    protected function filterQueryForList(EloquentBuilder $query, FeatureList $list): EloquentBuilder
    {
        return $query->whereRelation('list', $list->getQualifiedKeyName(), $list->getKey());
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<TItem>  $query
     * @param  PaginationArgs  $paginationArgs
     * @param null|array{
     *     search?: string,
     *     filter?: string,
     *     markers?: string[],
     *     includeGroups?: string[],
     *     excludeGroups?: string[],
     * } $filters
     * @param  OrderBy  $orderBy
     * @param  \Illuminate\Database\Eloquent\Builder<TItem>  $query
     * @return array{
     *     groups: mixed[],
     * }
     *
     * @phpstan-ignore-next-line Not sure how to resolve this
     */
    protected function buildGroupedPaginator(
        string $group,
        EloquentBuilder $query,
        array $paginationArgs,
        ?Builder $rawQuery,
        ?array $orderBy = null,
        ?array $filters = null,
    ): array {
        $groups = $this->fetchGroups($group, Arr::only($filters ?? [], ['includeGroups', 'excludeGroups', 'space']));

        return [
            'groups' => $groups->map(function ($groupHeader) use ($group, $query, $paginationArgs, $rawQuery, $orderBy) {
                /** @phpstan-ignore-next-line Not sure how to resolve this */
                $groupQuery = $this->filterQueryForGroup(clone $query, $group, $groupHeader);
                /** @phpstan-ignore-next-line Not sure how to resolve this */
                $deferred = $this->buildPaginator($groupQuery, $paginationArgs, $rawQuery, $orderBy);

                $paginatorHeader = $this->getGroupHeaderId($group, $groupHeader);

                return $deferred->then(function ($paginator) use ($paginatorHeader, $groupHeader) {
                    $paginator->groupHeader = $paginatorHeader;
                    if ($paginatorHeader !== $groupHeader) {
                        $paginator->group = $groupHeader;
                    } else {
                        $paginator->group = null;
                    }

                    return $paginator;
                });
            })->all(),
        ];
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<TItem>  $query
     * @param  PaginationArgs  $paginationArgs
     *
     * @phpstan-ignore-next-line Not sure why it doesn't like this
     */
    protected function buildPaginator(
        EloquentBuilder $query,
        array $paginationArgs,
        ?Builder $rawQuery,
        ?array $orderBy = null,
    ): SyncPromise {
        return $this->paginateQuery($query, $paginationArgs, function (Paginator $lampager) use ($orderBy) {
            if ($orderBy) {
                $lampager->builder->addSelect($lampager->builder->qualifyColumn('*'));
                foreach ($orderBy as $orders) {
                    $column = $orders['field'];
                    $direction = $orders['direction'];

                    $this->applyOrderBy($lampager, $column, $direction);
                }
            }
            $lampager->orderByDesc(
                $lampager->builder->getModel() instanceof Sortable
                    ? $lampager->builder->qualifyColumn('order')
                    : $lampager->builder->getModel()->getQualifiedKeyName()
            );

            /*
             * Ok so what is going on here eh?
             * Well Postgres doesn't allow filtering on a computed column as is
             * created when sorting by the list. The solution is to use
             * a sub select query.
             * So here we get the columns from the original query and then
             * change to select all. Creating a new sub query to specify the
             * columns.
             */
            $base = $lampager->builder->getQuery();
            $columns = $base->columns ?: ['*'];
            $hasAs = false;
            foreach ($columns as $column) {
                $grammar = $base->getGrammar();
                $column = $grammar->getValue($column);
                if (preg_match('/ as /i', $column)) {
                    $hasAs = true;
                    break;
                }
            }
            if ($hasAs) {
                $table = $lampager->builder->getModel()->getTable();
                $joins = $base->joins;
                $base->joins = [];
                $base->select();
                $base->fromSub(function ($query) use ($table, $columns, $joins) {
                    $query->from($table);
                    $query->select($columns);
                    $query->joins = $joins;
                }, $table);
            }
        }, $rawQuery);
    }

    protected function applyOrderBy(Paginator $lampager, string $column, string $direction): void
    {
        if ($column === $this->getListOrderByField()) {
            $this->applyListOrderBy($lampager, $direction);
        } else {
            $this->applyColumnOrderBy($lampager, $column, $direction);
        }
    }

    protected function applyColumnOrderBy(Paginator $lampager, string $column, string $direction): void
    {
        $lampager->orderBy(
            $lampager->builder->qualifyColumn(strtolower($column)),
            $direction
        );
    }

    protected function applyListOrderBy(Paginator $lampager, string $direction): void
    {
        $lampager->orderBy('list_order', $direction);
    }

    /**
     * @phpstan-ignore-next-line Spent too long on this, can't figure out how to make this work
     */
    protected function applyFilters(EloquentBuilder $query, array $filters): EloquentBuilder
    {
        if (isset($filters['isFavorited'])) {
            $method = $filters['isFavorited'] ? 'whereNotNull' : 'whereNull';
            $query->$method('favorited_at');
        }

        return $query;
    }

    abstract protected function getListOrderByField(): string;

    /**
     * @phpstan-ignore-next-line Spent too long on this, can't figure out how to make this work
     */
    abstract protected function getItemQuery(Base $base): EloquentBuilder;

    /**
     * @return \Illuminate\Database\Eloquent\Builder<TList>
     *
     * @phpstan-ignore-next-line Not sure why it doesn't like this
     */
    abstract protected function getListQuery(Base $base): EloquentBuilder;

    /**
     * @param  TList  $list
     * @return TItem
     */
    protected function createFeatureItemFromAttributes(FeatureList $list, array $data): FeatureListItem
    {
        /** @var TItem $item */
        $item = $list->children()->create($data);

        return $item;
    }

    /**
     * @param  TItem  $item
     * @return TItem
     */
    protected function updateFeatureItemFromAttributes(FeatureListItem $item, array $data): FeatureListItem
    {
        $item->fill($data);

        return $item;
    }

    /**
     * @param  TItem  $item
     * @return TItem
     */
    protected function duplicateFeatureItemFromAttributes(FeatureListItem $item, array $data): FeatureListItem
    {
        /** @phpstan-ignore-next-line */
        $item->name = $item->name.' (Copy)';

        $itemDuplicated = $item->replicate();
        $itemDuplicated->save();

        /** @phpstan-ignore-next-line Not sure why it doesn't like this */
        return $itemDuplicated;
    }

    abstract protected function getFeatureType(): MappingFeatureType;

    protected function getListKey(Base $base): string
    {
        $query = $this->getItemQuery($base);
        /** @var TItem $model */
        $model = $query->getModel();

        return lcfirst(Str::studly($model->list()->getForeignKeyName()));
    }

    /**
     * @phpstan-ignore-next-line Spent too long on this, can't figure out how to make this work
     */
    protected function getRawBuilder(Base $base, ?array $listIds, Item|string|null $node, int|Mapping|null $mapping, Space|int|null $space = null): EloquentBuilder
    {
        $query = $this->getItemQuery($base);

        if ($listIds !== null) {
            /** @phpstan-ignore-next-line Spent too long on this */
            $listModel = $query->getModel()->list()->getModel();
            $query->whereIn($listModel->getForeignKey(), $listIds);
        }

        // Node filter is more specific so ignore mapping if node exists
        if ($mapping && ! $node) {
            if (\is_int($mapping)) {
                $mapping = $base->mappings()->findOrFail($mapping);
            }
            $query->whereRelation('items', 'mapping_id', $mapping->id);
        }

        if ($node) {
            if (\is_string($node)) {
                $node = Utils::resolveModelFromGlobalId($node);
            }
            $query->whereRelation(
                'items',
                $node->getQualifiedKeyName(),
                $node->getKey(),
            );
        }

        if ($space && ! $mapping && ! $node) {
            if (\is_int($space)) {
                $space = $base->spaces()->findOrFail($space);
            }
            $query->whereRelation('list', 'space_id', $space->id);
        }

        return $query;
    }

    /**
     * @phpstan-ignore-next-line Spent too long on this, can't figure out how to make this work
     */
    protected function applyFiltersToQuery(EloquentBuilder $query, array $filters, string $bool = 'AND'): void
    {
        if (array_is_list($filters)) {
            $query->where(function (EloquentBuilder $query) use ($filters, $bool) {
                foreach ($filters as $filter) {
                    $this->applyFiltersToQuery($query, $filter, $bool);
                }
            });
        }

        if ($filters['search'] ?? null) {
            $method = $bool === 'AND' ? 'where' : 'orWhere';
            $query->$method(function ($query) use ($filters) {
                foreach ($filters['search'] as $search) {
                    $query->orWhere(function (EloquentBuilder $query) use ($search) {
                        $search = preg_replace('/[%_]/', '\\\\\1', $search); // Escape % and _ for ilike

                        foreach ($this->getSearchFields() as $field) {
                            $query->orWhere($query->qualifyColumn($field), ilike(), "%$search%");
                        }
                    });
                }
            });
        }

        if ($filters['markers'] ?? []) {
            $method = $bool === 'AND' ? 'whereHas' : 'orWhereHas';
            $query->$method('markers', function (/** @var \Illuminate\Database\Eloquent\Relations\HasMany $query */ $query) use ($filters) {
                /** @var array<array{
                 *     markerId: string,
                 *     operator: 'IS' | 'IS_NOT',
                 * }> $markers
                 */
                $markers = $filters['markers'];
                [$hasMarkers, $hasNotMarkers] = collect($markers)
                    ->map(fn ($marker) => [...$marker, 'markerId' => $this->globalId->decodeID($marker['markerId'])])
                    ->partition(fn ($marker) => $marker['operator'] === MarkerFilterOperator::IS->value);
                if ($hasMarkers) {
                    $query->whereKey($hasMarkers->pluck('markerId'));
                }
                if ($hasNotMarkers) {
                    $query->whereKeyNot($hasNotMarkers->pluck('markerId'));
                }
            });
        }

        $this->applyFilters($query, $filters);

        if ($filters['filters'] ?? []) {
            $this->applyFiltersToQuery($query, $filters['filters'], $filters['boolean'] ?? 'AND');
        }
    }

    /**
     * @return string[]
     */
    protected function getSearchFields(): array
    {
        return ['name', 'description'];
    }

    /**
     * @param array{
     *     markers?: array<array{
     *         groupId: int,
     *         markers: int[],
     *     }>,
     * } $args
     * @return \App\Models\Marker[]
     */
    protected function getMarkersFromInput(Base $base, Space $space, array $args, MappingFeatureType $type): array
    {
        $markers = collect();
        foreach ($args['markers'] ?? [] as $markerInput) {
            $enabledMarkerGroupIds = $space->markerGroupsWithEnabledFeatures([$type]);
            $markers = $markers->merge(
                $base->markers()
                    ->whereHas('group', function (EloquentBuilder $query) use ($markerInput, $enabledMarkerGroupIds) {
                        $query->whereKey($markerInput['groupId'])
                            ->whereKey($enabledMarkerGroupIds);
                    })
                    ->with('group')
                    ->findOrFail($markerInput['markers'])
            );
        }

        return $markers->all();
    }

    /**
     * @param array{
     *     assigneeGroups?: array<array{
     *         groupId: int,
     *         assignees: int[],
     *     }>,
     * } $args
     * @return array<array{
     *     group: \App\Models\AssigneeGroup,
     *     assignees: int[],
     * }>
     */
    protected function getAssigneesFromInput(Base $base, array $args): array
    {
        $assigneeInput = collect($args['assigneeGroups'] ?? []);
        $assigneeIds = $assigneeInput->pluck('assignees')->collapse();
        /** @var \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $assignees */
        $assignees = $base->members()
            ->wherePivotIn('id', $assigneeIds)
            ->get()->pluck('pivot')
            ->keyBy('id');
        $groups = $base->assigneeGroups()->findOrFail($assigneeInput->pluck('groupId'))->keyBy('id');

        return $assigneeInput->map(function (array $input) use ($assignees, $groups) {
            /** @var \App\Models\AssigneeGroup $group */
            $group = $groups[$input['groupId']];

            return [
                'group' => $group,
                'assignees' => $assignees->only($input['assignees'])->pluck('id')->all(),
            ];
        })->all();
    }

    /**
     * @param array{
     *     associations?: int[],
     * } $args
     * @return \App\Models\Item[]
     */
    protected function getAssociatedItems(Base $base, array $args, Space $space, MappingFeatureType $type): array
    {
        $items = collect();
        if (isset($args['associations'])) {
            /** @var \Illuminate\Database\Eloquent\Collection<int, \App\Models\Item> $items */
            $items = $base->items()
                ->whereRelation('mapping', 'space_id', $space->id)
                ->findOrFail($args['associations']);
            $notAllowedItems = $items->filter(fn (Item $item) => ! $item->mapping->featureEnabled($type));
            if ($notAllowedItems->isNotEmpty()) {
                throw (new ModelNotFoundException)->setModel(Item::class, $notAllowedItems->modelKeys());
            }
        }

        return $items->all();
    }
}
