<?php

declare(strict_types=1);

namespace App\Core\Mappings\Repositories;

use App\Models\Item;
use App\Models\Marker;
use App\Models\Mapping;
use Lampager\Paginator;
use App\Models\MarkerGroup;
use Illuminate\Support\Str;
use LighthouseHelpers\Utils;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;
use App\GraphQL\Queries\Concerns\GroupsQueries;
use GraphQL\Executor\Promise\Adapter\SyncPromise;
use App\GraphQL\Queries\Concerns\PaginatesQueries;
use LighthouseHelpers\Exceptions\ModelNotFoundException;
use Illuminate\Database\Query\Builder as IlluminateBuilder;

class EloquentItemRepository
{
    /** @use \App\GraphQL\Queries\Concerns\GroupsQueries<\App\Models\Item> */
    use GroupsQueries;

    use PaginatesQueries;

    protected ?Mapping $mapping;

    /**
     * @param  PaginationArgs  $paginationArgs
     */
    public function readPage(array $paginationArgs, ?ItemFilter $filter = null, ?array $order = [], ?string $group = null): SyncPromise|array
    {
        $this->mapping = $filter?->getMapping();

        $query = $this->getRawBuilder($filter);

        $rawQuery = clone $query->toBase();

        $query = $this->applyFiltersToQuery($query, $filter);

        if ($group) {
            return $this->buildGroupedPaginator($group, $query, $paginationArgs, $rawQuery, $order, $filter);
        }

        // Necessary for sqlite
        $query->select('items.*');

        return $this->buildPaginator($query, $paginationArgs, $rawQuery, $order);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder<\App\Models\Item>
     */
    protected function getRawBuilder(?ItemFilter $filter): Builder
    {
        $query = Item::query();
        if ($relationFilter = $filter?->getRelation()) {
            /** @var \App\Models\Item $item */
            $item = Item::query()->findOrFail($relationFilter['itemId']);
            /** @var \Mappings\Core\Mappings\Relationships\Relationship|null $relation */
            $relation = $item->mapping->relationships->find($relationFilter['relationId']);
            if (! $relation) {
                throw new ModelNotFoundException('Could not find relationship ['.$relationFilter['relationId'].'] in mapping ['.$item->mapping->id.']');
            }
            /** @var \Illuminate\Database\Eloquent\Builder<\App\Models\Item> $query */
            $query = $item->relatedItems($relation)->getQuery();
        }

        if ($mapping = $filter?->getMapping()) {
            $query->where('mapping_id', $mapping->id);
        }

        return $query;
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<\App\Models\Item>  $query
     * @return \Illuminate\Database\Eloquent\Builder<\App\Models\Item>
     */
    protected function applyFiltersToQuery(Builder $query, ?ItemFilter $filter): Builder
    {
        return $query;
    }

    /**
     * @return \Illuminate\Support\Collection<int, \App\Models\Marker|null>
     *
     * @throws \Nuwave\Lighthouse\Exceptions\ValidationException
     *
     * Overrides trait method because the marker group id could be from the mapping
     */
    protected function getMarkerHeaders(string $id): Collection
    {
        $mappingMarkerGroup = $this->mapping?->markerGroups?->find($id);
        $markerGroup = $mappingMarkerGroup
            ? $mappingMarkerGroup->markerGroup()
            : Utils::resolveModelFromGlobalId($id);

        if (! $markerGroup instanceof MarkerGroup) {
            $this->throwInvalidGroup();
        }

        // Need to clone the collection as it gets cached on the model with
        // octane.
        /** @phpstan-ignore-next-line We are adding a null value for the null group */
        return (clone $markerGroup->markers)->push(null);
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<T>  $query
     * @return \Illuminate\Database\Eloquent\Builder<T>
     *
     * @phpstan-ignore-next-line Not sure how to resolve this
     */
    protected function filterQueryForMarker(Builder $query, ?Marker $marker, string $id): Builder
    {
        $mappingMarkerGroup = $this->mapping?->markerGroups?->find($id);

        if (! $marker) {
            $groupId = $mappingMarkerGroup
                ? $mappingMarkerGroup->group
                : $this->getGlobalId()->decodeId($id);

            return $query->whereDoesntHave('markers', function (Builder $query) use ($groupId, $mappingMarkerGroup) {
                $query->where('marker_group_id', $groupId);
                if ($mappingMarkerGroup) {
                    $query->where('markables.context', $mappingMarkerGroup->id());
                }
            });
        }

        return $query->whereHas('markers', function (Builder $query) use ($marker, $mappingMarkerGroup) {
            $query->where($marker->getQualifiedKeyName(), $marker->getKey());
            if ($mappingMarkerGroup) {
                $query->where('markables.context', $mappingMarkerGroup->id());
            }
        });
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<\App\Models\Item>  $query
     * @param  PaginationArgs  $paginationArgs
     */
    protected function buildPaginator(
        Builder $query,
        array $paginationArgs,
        ?IlluminateBuilder $rawQuery,
        ?array $orderBy = null,
    ): SyncPromise {
        return $this->paginateQuery($query, $paginationArgs, function (Paginator $lampager) use ($orderBy) {
            $order = $orderBy ?? [];
            if (! collect($order)->contains('field', 'id')) {
                $order[] = ['field' => 'id', 'direction' => 'desc'];
            }
            foreach ($order as $orderByClause) {
                $lampager->orderBy(
                    $lampager->builder->qualifyColumn(
                        Str::snake(mb_strtolower($orderByClause['field']))
                    ),
                    mb_strtolower($orderByClause['direction'])
                );
            }
        }, $rawQuery);
    }

    /**
     * @param  PaginationArgs  $paginationArgs
     * @param  OrderBy  $orderBy
     * @param  \Illuminate\Database\Eloquent\Builder<\App\Models\Item>  $query
     * @return array{
     *     groups: mixed[],
     * }
     */
    protected function buildGroupedPaginator(
        string $group,
        Builder $query,
        array $paginationArgs,
        ?IlluminateBuilder $rawQuery,
        ?array $orderBy = null,
        ?ItemFilter $filter = null,
    ): array {
        $groups = $this->fetchGroups($group, [
            'includeGroups' => $filter?->getIncludeGroups() ?: [],
            'excludeGroups' => $filter?->getExcludeGroups() ?: [],
        ]);

        return [
            'groups' => $groups->map(function ($groupHeader) use ($group, $query, $paginationArgs, $rawQuery, $orderBy) {
                $groupQuery = $this->filterQueryForGroup(clone $query, $group, $groupHeader);
                $deferred = $this->buildPaginator($groupQuery, $paginationArgs, $rawQuery, $orderBy);

                return $deferred->then(function ($paginator) use ($group, $groupHeader) {
                    $paginator->groupHeader = $this->getGroupHeaderId($group, $groupHeader);
                    if ($paginator->groupHeader !== $groupHeader) {
                        $paginator->group = $groupHeader;
                    } else {
                        $paginator->group = null;
                    }

                    return $paginator;
                });
            })->all(),
        ];
    }
}
