<?php

declare(strict_types=1);

namespace App\Core\Features\Repositories;

use App\Models\Base;
use App\Models\Item;
use App\Models\Space;
use App\Models\Mapping;
use Lampager\Paginator;
use LighthouseHelpers\Utils;
use App\GraphQL\CountModelsLoader;
use App\Models\Contracts\FeatureList;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use GraphQL\Executor\Promise\Adapter\SyncPromise;
use App\GraphQL\Queries\Concerns\PaginatesQueries;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Nuwave\Lighthouse\Execution\BatchLoader\BatchLoaderRegistry;
use Nuwave\Lighthouse\Execution\BatchLoader\RelationBatchLoader;

/**
 * @template TList of \App\Models\Contracts\FeatureList
 */
abstract class FeatureListRepository
{
    use PaginatesQueries;

    public function getDeferredItemCountForList(
        Base $base,
        Model $rootValue,
        array $path,
        Item|string|null $node = null,
        Mapping|int|null $mapping = null,
    ): SyncPromise {
        return BatchLoaderRegistry::instance(
            array_merge(
                $path,
                ['count']
            ),
            static function () use ($base, $mapping, $node): RelationBatchLoader {
                return new RelationBatchLoader(
                    new CountModelsLoader('children', function (HasMany|Builder $query) use ($base, $mapping, $node) {
                        if ($mapping) {
                            if (\is_int($mapping)) {
                                $mapping = $base->mappings()->findOrFail($mapping);
                            }
                            $query->whereRelation('items', 'mapping_id', $mapping->id);
                        }
                        if ($node) {
                            if (\is_string($node)) {
                                $node = Utils::resolveModelFromGlobalId($node);
                            }
                            $query->whereRelation('items', 'items.id', $node->getKey());
                        }
                    })
                );
            }
        )->load($rootValue);
    }

    /**
     * @param  int[]|\App\Models\Space[]|\Illuminate\Database\Eloquent\Collection<int, \App\Models\Space>|null  $spaces
     * @param  PaginationArgs  $paginationArgs
     * @param  int[]  $listIds
     *
     * @throws \JsonException
     */
    public function paginateFeatureLists(
        Base $base,
        array $paginationArgs,
        Collection|array|null $spaces = null,
        ?array $listIds = null,
        ?array $refs = null,
    ): SyncPromise {
        $query = $this->getListQuery($base);

        if (isset($spaces)) {
            /** @phpstan-ignore-next-line Grr */
            $spaceIds = collect($spaces)->map(function (Space|int $space) {
                return ($space instanceof Space) ? $space->getKey() : $space;
            });
            $query->whereIn('space_id', $spaceIds);
        }

        if ($listIds !== null) {
            $query->whereKey($listIds);
        }
        if ($refs !== null) {
            if (empty($refs)) {
                $query->whereRaw('0 = 1');
            } else {
                $query->where(array_map(function ($ref) {
                    $ref = mb_strtoupper($ref);

                    return ['template_refs', 'like', "%$ref%"];
                }, $refs));
            }
        }

        return $this->paginateQuery($query, $paginationArgs, function (Paginator $lampager) {
            $lampager->orderBy('order');
        });
    }

    /**
     * @return TList
     */
    public function createFeatureList(Base $base, array $data): FeatureList
    {
        $data['is_default'] = false;

        $query = $this->getListQuery($base);

        $copy = 1;
        $name = $data['name'];
        while ((clone $query)->where('name', $name)->exists()) {
            $name = "{$data['name']} ($copy)";
            $copy++;
        }
        $data['name'] = $name;

        /** @phpstan-ignore-next-line Spent too long on this */
        return $query->create($data);
    }

    /**
     * @return TList
     */
    public function updateFeatureList(Base $base, int $id, array $data): FeatureList
    {
        $list = $this->getFeatureList($base, $id);

        $list->update($data);

        return $list;
    }

    public function deleteFeatureList(Base $base, int $id, bool $force = false): ?bool
    {
        $list = $this->getFeatureList($base, $id, $force);

        if ($force) {
            return $list->forceDelete();
        }

        return $list->delete();
    }

    /**
     * @return TList|null
     */
    public function restoreFeatureList(Base $base, int $id): ?FeatureList
    {
        $list = $this->getFeatureList($base, $id, true);

        if ($list->trashed()) {
            $success = $list->restore();

            return $success ? $list : null;
        }

        return null;
    }

    /**
     * @return TList
     */
    public function moveFeatureList(Base $base, int $id, ?int $previousId): FeatureList
    {
        $list = $this->getFeatureList($base, $id);

        if ($previousId) {
            $previousCalendar = $this->getFeatureList($base, $previousId);
            $list->moveBelow($previousCalendar);
        } else {
            $list->moveToStart();
        }

        return $list;
    }

    /**
     * @return TList
     */
    public function getFeatureList(Base $base, int $id, bool $withTrashed = false): FeatureList
    {
        $query = $this->getListQuery($base);
        if ($withTrashed) {
            /** @phpstan-ignore-next-line We know this exists */
            $query->withTrashed();
        }

        /** @phpstan-ignore-next-line Spent too long on this */
        return $query->findOrFail($id);
    }

    /**
     * @phpstan-ignore-next-line Spent too long on this
     */
    abstract protected function getListQuery(Base $base): Builder;
}
