<?php

declare(strict_types=1);

namespace App\GraphQL;

use GraphQL\Deferred;
use Illuminate\Support\Collection;
use LighthouseHelpers\Pagination\Cursor;
use Illuminate\Database\Eloquent\Builder;
use LighthouseHelpers\Pagination\CursorProcessor;
use LighthouseHelpers\Pagination\PaginationResult;
use Illuminate\Database\Eloquent\Relations\Relation;
use LaravelUtils\Database\Eloquent\Scopes\LatestIfNotOrderedScope;
use Illuminate\Contracts\Database\Query\Builder as BuilderContract;
use LaravelUtils\Database\Eloquent\Scopes\DefaultOrderIfNotOrderedScope;

class PaginatorBatchLoader
{
    /**
     * @var \Illuminate\Support\Collection<int, array{
     *     0: PaginationArgs,
     *     1: \Illuminate\Contracts\Database\Query\Builder ,
     *     2: null|\Closure(\Lampager\Laravel\Paginator): mixed,
     *     3: \Illuminate\Contracts\Database\Query\Builder|null,
     * }>
     */
    protected Collection $queries;

    /**
     * @var \WeakMap<\Illuminate\Contracts\Database\Query\Builder, \LighthouseHelpers\Pagination\PaginationResult>
     */
    protected \WeakMap $resultsMap;

    /**
     * Marks when the actual batch loading happened.
     */
    protected bool $hasResolved = false;

    public function __construct()
    {
        $this->resultsMap = new \WeakMap;
        $this->queries = new Collection;
    }

    /**
     * Loaders work by storing all the information you need to build the
     * response and then resolving all the requested data at once and storing it
     * in a map so each request can pick off the data it needs.
     *
     * Now ideally we would make one simple query and then split the results
     * up like the SimpleModelsLoader does, but Hylark is anything but simple,
     * so we can't do that. Instead, the next best thing is to combine all the
     * queries to the same table into one big union query and then split the
     * results up from there.
     * This is not the most optimised query, but it does save us a lot of time
     * in extra calls to the database server, so it's much better than nothing.
     *
     * @param  PaginationArgs  $args
     * @param  \Closure(\Lampager\Laravel\Paginator): mixed|null  $lampagerClosure
     */
    public function load($args, BuilderContract $query, ?\Closure $lampagerClosure = null, ?BuilderContract $rawQuery = null): Deferred
    {
        // The raw query should be the query that doesn't have any filters applied.
        // This is used to give the user the right information about the results they are seeing.
        // If the raw query and the filtered query look the same then we can skip the
        // calculations for the raw counts.
        if ($rawQuery && $query->toSql() === $rawQuery->toSql()
            && $query->getBindings() === $rawQuery->getBindings()
        ) {
            $rawQuery = null;
        }
        $this->queries[] = [$args, $query, $lampagerClosure, $rawQuery];

        return new Deferred(function () use ($query) {
            if (! $this->hasResolved) {
                $this->resolve();
            }

            return $this->resultsMap[$query];
        });
    }

    /**
     * This is where all the heavy lifting is done, we need to separate the
     * process into multiple sections.
     * 1. Get all the counts for the different queries that will be paginated.
     *    If there are any queries that are empty then we can skip adding it to
     *    the union query.
     * 2. Build and execute the union query.
     * 3. Split the results into the different paginators that need the data.
     */
    protected function resolve(): void
    {
        // 1. Get all the counts for the different queries.
        $totals = $this->getQueryCounts($this->queries->pluck(1));

        $rawQueries = $this->queries->pluck(3);
        $rawTotals = $totals;
        if (! $rawQueries->contains(null)) {
            $rawTotals = $this->getQueryCounts($rawQueries);
        }

        /** @var \Illuminate\Support\Collection<int, array{0: \Lampager\Laravel\Paginator, 1: \Lampager\Query, 2: \Illuminate\Contracts\Database\Query\Builder}> $paginatedQueries */
        $paginatedQueries = new Collection;

        // 2. Build the union query with using the underlying methods of Lampager so
        // we can leverage all that pagination goodness.
        foreach ($totals as $index => $total) {
            /** @phpstan-ignore-next-line It doesn't know what it's talking about */
            [$args, $query, $lampagerClosure] = $this->queries[$index];
            if (! $total) {
                $paginator = $this->emptyPaginator();
                $this->addBaseClass($query, $paginator);
                $this->resultsMap[$query] = $paginator;
            } else {
                /** @var \Lampager\Laravel\Paginator $lampager */
                $lampager = $query->lampager();

                $lampager = $lampager->useProcessor(CursorProcessor::class);

                if ($lampagerClosure) {
                    $lampagerClosure($lampager);
                } else {
                    $lampager->orderBy($lampager->builder->qualifyColumn('id'))
                        ->forward();
                }

                $lampager->limit($args['first'])
                    ->seekable()
                    ->exclusive();

                // This bit is what happens under the hood when you call
                // `$lampager->paginate()` without the call to `->process()`
                // because that happens after we have collected the results
                // and split them up.
                $cursor = Cursor::decode($args);
                $lampagerQuery = $lampager->configure($cursor);
                $paginatedQuery = $lampager->transform($lampagerQuery);

                // Now the problem with union queries is that each row doesn't
                // include any information about which query it came from, so
                // here we add a column `__index` which references the index in
                // the `$paginatedQueries` array, and so we can group by that
                // to get the separate results.
                $this->addIndexToQueryAndUnions($paginatedQuery, $index);

                $paginatedQueries[$index] = [$lampager, $lampagerQuery, $paginatedQuery];
            }
        }

        // If there are no queries (i.e. all results are empty) then we don't
        // need to do anything else.
        if ($paginatedQueries->isNotEmpty()) {
            // 2. Build and execute the union query.
            $queries = $paginatedQueries->pluck(2);
            $firstPaginatedQuery = $queries->shift();
            $fullPaginatedQuery = $queries->reduce(function ($carry, $query) {
                return $carry->unionAll($query);
            }, $firstPaginatedQuery);

            $paginatedResults = $fullPaginatedQuery->get()->groupBy('__index');

            // 3. Split the results into the different paginators that need the data.
            foreach ($paginatedResults as $index => $paginatedResult) {
                /** @phpstan-ignore-next-line We know the index exists */
                $query = $this->queries[$index][1];
                /** @phpstan-ignore-next-line We know the index exists */
                $lampagerQuery = $paginatedQueries[$index][1];
                /** @phpstan-ignore-next-line We know the index exists */
                $lampager = $paginatedQueries[$index][0];
                // Here is the call to `->process()` that we skipped earlier.
                $paginator = $lampager->process($lampagerQuery, $paginatedResult);
                // Adding the base class to the paginator, so we can use it to
                // resolve the connection in the schema.
                $this->addBaseClass($query, $paginator);
                $this->resultsMap[$query] = $paginator;
            }
        }

        // Finally we add the counts that we calculated at the start.
        foreach ($this->queries as $index => $query) {
            /** @var \LighthouseHelpers\Pagination\PaginationResult $results */
            $results = $this->resultsMap[$query[1]];
            $results->total = $totals[$index] ?? 0;
            $results->rawTotal = $rawTotals[$index] ?? 0;
        }

        $this->hasResolved = true;
    }

    protected function addIndexToQueryAndUnions(BuilderContract $query, int $index): void
    {
        $baseQuery = get_base_query($query);
        if (! $baseQuery->columns) {
            $query->addSelect('*');
        }
        $query->selectRaw("$index as __index");

        if ($baseQuery->unions) {
            foreach ($baseQuery->unions as $union) {
                $this->addIndexToQueryAndUnions($union['query'], $index);
            }
        }
    }

    /**
     * @param  \Illuminate\Support\Collection<int, \Illuminate\Contracts\Database\Query\Builder>  $queries
     * @return \Illuminate\Support\Collection<int, int>
     */
    protected function getQueryCounts(Collection $queries): Collection
    {
        /** @var \Illuminate\Contracts\Database\Query\Builder $firstQuery */
        $firstQuery = $queries->shift();

        $query = $queries->reduce(function ($carry, $query, $index) {
            return $carry->unionAll($this->buildCountQuery($query, $index + 1));
        }, $this->buildCountQuery($firstQuery, 0));

        return $query->get()->pluck('aggregate', '__index');
    }

    /**
     * This is a copy of the `Illuminate\Database\Query\Builder::getCountForPagination`
     * which is protected, so we can't use it directly.
     */
    protected function buildCountQuery(BuilderContract $query, int $index): BuilderContract
    {
        $clonedQuery = clone $query;
        $base = get_base_query($clonedQuery);
        foreach (['columns', 'orders', 'limit', 'offset'] as $key) {
            /** @phpstan-ignore-next-line If Laravel can do it, so can I */
            $base->{$key} = null;
        }
        foreach (['select', 'order'] as $bindingKey) {
            $base->bindings[$bindingKey] = [];
        }
        if ($clonedQuery instanceof Builder || $clonedQuery instanceof Relation) {
            /** @phpstan-ignore-next-line Both can access the method */
            $clonedQuery->withoutGlobalScopes([
                DefaultOrderIfNotOrderedScope::class,
                LatestIfNotOrderedScope::class,
            ]);
        }

        return $clonedQuery->selectRaw("count(*) as aggregate, $index as __index");
    }

    protected function emptyPaginator(): PaginationResult
    {
        return new PaginationResult(collect(), [
            'hasPrevious' => false,
            'previousCursor' => null,
            'hasNext' => false,
            'nextCursor' => null,
        ], 0, 0);
    }

    protected function addBaseClass(BuilderContract $query, PaginationResult $paginator): void
    {
        if ($query instanceof Relation) {
            $paginator->baseClass = get_class($query->getRelated());
        } elseif ($query instanceof Builder) {
            $paginator->baseClass = get_class($query->getModel());
        }
    }
}
