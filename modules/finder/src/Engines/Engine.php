<?php

declare(strict_types=1);

namespace Finder\Engines;

use Finder\Builder;
use Finder\GloballySearchable;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

abstract class Engine
{
    /**
     * @param  \Illuminate\Database\Eloquent\Collection<int, \Illuminate\Database\Eloquent\Model&\Finder\GloballySearchable>  $models
     */
    abstract public function update(EloquentCollection $models): void;

    /**
     * @param  \Illuminate\Database\Eloquent\Collection<int, \Illuminate\Database\Eloquent\Model&\Finder\GloballySearchable>  $models
     */
    abstract public function delete(EloquentCollection $models): void;

    abstract public function search(Builder $builder): mixed;

    abstract public function paginate(Builder $builder, int $perPage, int $page): mixed;

    abstract public function cursorPaginate(Builder $builder, int $perPage, ?array $cursor): mixed;

    /**
     * @return \Illuminate\Support\Collection<int, string|int>
     */
    abstract public function mapIds(mixed $results): Collection;

    /**
     * @return \Illuminate\Support\Collection<int, \Illuminate\Database\Eloquent\Model&\Finder\GloballySearchable>
     */
    abstract public function map(Builder $builder, mixed $results): Collection;

    abstract public function getTotalCount(mixed $results): ?int;

    abstract public function flush(GloballySearchable $model): void;

    abstract public function createIndex(string $name, array $options = []): void;

    abstract public function deleteIndex(string $name): void;

    public function mapIdsFrom(mixed $results): mixed
    {
        return $this->mapIds($results);
    }

    public function keys(Builder $builder): mixed
    {
        return $this->mapIds($this->search($builder));
    }

    /**
     * @return \Illuminate\Support\Collection<int, \Illuminate\Database\Eloquent\Model&\Finder\GloballySearchable>
     */
    public function get(Builder $builder): Collection
    {
        return $this->map(
            $builder, $this->search($builder)
        );
    }
}
