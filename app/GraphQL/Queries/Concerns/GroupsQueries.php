<?php

declare(strict_types=1);

namespace App\GraphQL\Queries\Concerns;

use App\Models\Marker;
use App\Models\MarkerGroup;
use Illuminate\Support\Str;
use LighthouseHelpers\Utils;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Nuwave\Lighthouse\GlobalId\GlobalId;
use Illuminate\Database\Eloquent\Builder;
use LighthouseHelpers\Exceptions\ValidationException;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

/**
 * @template T of \Illuminate\Database\Eloquent\Model
 */
trait GroupsQueries
{
    protected function getGlobalId(): GlobalId
    {
        return resolve(GlobalId::class);
    }

    /**
     * @return never
     *
     * @throws \Nuwave\Lighthouse\Exceptions\ValidationException
     */
    protected function throwInvalidGroup(): void
    {
        throw ValidationException::withMessages(['group' => 'Invalid group']);
    }

    /**
     * @return \Illuminate\Support\Collection<int, mixed>
     */
    protected function fetchGroups(string $group, array $filters): Collection
    {
        $groups = $this->getGroupHeaders($group, $filters);

        $includeGroups = $filters['includeGroups'] ?? [];
        $excludeGroups = $filters['excludeGroups'] ?? [];

        if ($groups instanceof EloquentCollection) {
            $globalId = $this->getGlobalId();
            $includeGroups = array_map(fn (?string $id) => $id ? $globalId->decodeID($id) : $id, $includeGroups);
            $excludeGroups = array_map(fn (?string $id) => $id ? $globalId->decodeID($id) : $id, $excludeGroups);
        }

        return $groups
            ->when($includeGroups, function (Collection $groups) use ($includeGroups) {
                if ($groups instanceof EloquentCollection) {
                    return $groups->whereIn('id', $includeGroups);
                }

                return $groups->intersect($includeGroups);
            })
            ->when($excludeGroups, function (Collection $groups) use ($excludeGroups) {
                if ($groups instanceof EloquentCollection) {
                    return $groups->whereNotIn('id', $excludeGroups);
                }

                return $groups->diff($excludeGroups);
            });
    }

    /**
     * @return \Illuminate\Support\Collection<int, string>
     */
    protected function getFavoritesHeaders(): Collection
    {
        return collect(['1', '0']);
    }

    /**
     * @return \Illuminate\Support\Collection<int, string>
     */
    protected function getPriorityHeaders(): Collection
    {
        return collect(['0', '1', '3', '5', '9']);
    }

    /**
     * @return array{0: string, 1: string|null}
     */
    protected function explodeGroup(string $group): array
    {
        $id = null;
        if (Str::contains($group, ':')) {
            [$group, $id] = explode(':', $group);
        }

        return [$group, $id];
    }

    /**
     * @return \Illuminate\Support\Collection<int, mixed>
     */
    protected function getGroupHeaders(string $group, array $filters = []): Collection
    {
        [$group, $id] = $this->explodeGroup($group);
        $method = 'get'.Str::studly($group).'Headers';
        if (! method_exists($this, $method)) {
            $this->throwInvalidGroup();
        }

        return $this->$method($id, $filters);
    }

    /**
     * @return \Illuminate\Support\Collection<int, \App\Models\Marker|null>
     *
     * @throws \Nuwave\Lighthouse\Exceptions\ValidationException
     */
    protected function getMarkerHeaders(string $id): Collection
    {
        $markerGroup = Utils::resolveModelFromGlobalId($id);
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
        if (! $marker) {
            $id = $this->getGlobalId()->decodeId($id);

            return $query->whereDoesntHave('markers', fn (Builder $query) => $query->where('marker_group_id', $id));
        }

        return $query->whereRelation('markers', $marker->getQualifiedKeyName(), $marker->getKey());
    }

    protected function getGroupHeaderId(string $group, mixed $groupHeader): ?string
    {
        if ($groupHeader instanceof Model) {
            return $groupHeader->global_id ?? null;
        }

        return $groupHeader;
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<T>  $query
     * @return \Illuminate\Database\Eloquent\Builder<T>
     *
     * @phpstan-ignore-next-line Not sure how to resolve this
     */
    protected function filterQueryForGroup(Builder $query, string $group, mixed $groupHeader): Builder
    {
        [$group, $id] = $this->explodeGroup($group);

        $method = 'filterQueryFor'.Str::studly($group);
        if (method_exists($this, $method)) {
            return $this->$method($query, $groupHeader, $id);
        }

        return $query->where(mb_strtolower($group), $groupHeader);
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<T>  $query
     * @return \Illuminate\Database\Eloquent\Builder<T>
     *
     * @phpstan-ignore-next-line Not sure how to resolve this
     */
    protected function filterQueryForFavorites(Builder $query, mixed $groupHeader): Builder
    {
        /** @phpstan-ignore-next-line Not sure how to resolve this */
        return $query->whereNull('favorited_at', 'and', $groupHeader === '1');
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<T>  $query
     * @return \Illuminate\Database\Eloquent\Builder<T>
     *
     * @phpstan-ignore-next-line Not sure how to resolve this
     */
    protected function filterQueryForPriority(Builder $query, mixed $groupHeader): Builder
    {
        if ($groupHeader === '0') {
            return $query->where(function (Builder $query) {
                $query->whereNull('priority')
                    ->orWhere('priority', 0);
            });
        }

        return $query->where('priority', $groupHeader);
    }
}
