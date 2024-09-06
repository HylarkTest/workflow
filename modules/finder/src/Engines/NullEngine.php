<?php

declare(strict_types=1);

namespace Finder\Engines;

use Finder\Builder;
use Illuminate\Support\Collection as BaseCollection;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class NullEngine extends Engine
{
    /**
     * @param  \Illuminate\Database\Eloquent\Collection<int, \Illuminate\Database\Eloquent\Model&\Finder\GloballySearchable>  $models
     */
    public function update(EloquentCollection $models): void {}

    /**
     * @param  \Illuminate\Database\Eloquent\Collection<int, \Illuminate\Database\Eloquent\Model&\Finder\GloballySearchable>  $models
     */
    public function delete(EloquentCollection $models): void {}

    public function search(Builder $builder): mixed
    {
        return [];
    }

    public function paginate(Builder $builder, int $perPage, int $page): mixed
    {
        return [];
    }

    /**
     * @return \Illuminate\Support\Collection<int, string|int>
     */
    public function mapIds(mixed $results): BaseCollection
    {
        return BaseCollection::make();
    }

    /**
     * @return \Illuminate\Support\Collection<int, \Illuminate\Database\Eloquent\Model&\Finder\GloballySearchable>
     */
    public function map(Builder $builder, mixed $results): BaseCollection
    {
        return BaseCollection::make();
    }

    public function getTotalCount(mixed $results): int
    {
        return \count($results);
    }

    public function flush(mixed $model): void {}

    public function createIndex(string $name, array $options = []): void {}

    public function deleteIndex(string $name): void {}

    public function cursorPaginate(Builder $builder, int $perPage, ?array $cursor): mixed
    {
        return [];
    }
}
