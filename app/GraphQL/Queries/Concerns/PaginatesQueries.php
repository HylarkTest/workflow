<?php

declare(strict_types=1);

namespace App\GraphQL\Queries\Concerns;

use App\GraphQL\PaginatorBatchLoader;
use Illuminate\Contracts\Database\Query\Builder;
use GraphQL\Executor\Promise\Adapter\SyncPromise;
use LighthouseHelpers\Pagination\PaginationResult;
use Nuwave\Lighthouse\Execution\BatchLoader\BatchLoaderRegistry;

trait PaginatesQueries
{
    protected function emptyPaginator(): PaginationResult
    {
        return new PaginationResult(collect(), [
            'hasPrevious' => false,
            'previousCursor' => null,
            'hasNext' => false,
            'nextCursor' => null,
        ], 0, 0);
    }

    /**
     * @param  PaginationArgs  $args
     * @param  \Closure(\Lampager\Laravel\Paginator): mixed  $lampagerClosure
     *
     * @throws \JsonException
     */
    protected function paginateQuery(Builder $query, array $args, ?\Closure $lampagerClosure = null, ?Builder $rawQuery = null): SyncPromise
    {
        $baseQuery = get_base_query($query);
        $loader = BatchLoaderRegistry::instance(
            /** @phpstan-ignore-next-line Sometimes it is null */
            [$baseQuery->from, ...($baseQuery->columns ?? [])],
            fn (): PaginatorBatchLoader => new PaginatorBatchLoader,
        );

        return $loader->load(
            $args, $query, $lampagerClosure, $rawQuery,
        );
    }
}
