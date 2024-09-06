<?php

declare(strict_types=1);

namespace Finder;

use Finder\Engines\Engine;
use Illuminate\Support\Collection;
use Illuminate\Container\Container;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Traits\Macroable;
use Illuminate\Pagination\LengthAwarePaginator;
use LighthouseHelpers\Pagination\PaginationResult;

class Builder
{
    use Macroable;

    public ?Engine $engine = null;

    /**
     * @var ?callable(): mixed
     */
    public $queryCallback;

    /**
     * @var array<string, mixed>
     */
    public array $wheres = [];

    /**
     * @var array<string, mixed>
     */
    public array $whereIns = [];

    public int $limit;

    /**
     * The "order" that should be applied to the search.
     *
     * @var array<int, array{"column": string, "direction": "asc"|"desc"}>
     */
    public array $orders = [];

    public function __construct(
        public string $index,
        public ?string $query = null,
        public ?\Closure $callback = null,
        bool $softDelete = false
    ) {
        if ($softDelete) {
            $this->wheres['__soft_deleted'] = 0;
        }
    }

    public function index(string $index): static
    {
        $this->index = $index;

        return $this;
    }

    public function search(string $search): static
    {
        $this->query = $search;

        return $this;
    }

    public function where(string $field, mixed $value): static
    {
        $this->wheres[$field] = $value;

        return $this;
    }

    public function whereIn(string $field, array $values): static
    {
        $this->whereIns[$field] = $values;

        return $this;
    }

    public function withTrashed(): static
    {
        unset($this->wheres['__soft_deleted']);

        return $this;
    }

    public function onlyTrashed(): static
    {
        return tap($this->withTrashed(), function () {
            $this->wheres['__soft_deleted'] = 1;
        });
    }

    public function take(int $limit): static
    {
        $this->limit = $limit;

        return $this;
    }

    public function orderBy(string $column, string $direction = 'asc'): static
    {
        $this->orders[] = [
            'column' => $column,
            'direction' => mb_strtolower($direction) === 'asc' ? 'asc' : 'desc',
        ];

        return $this;
    }

    /**
     * @template T
     *
     * @param  T  $value
     * @param  \Closure(\Finder\Builder, T): mixed  $callback
     * @param  \Closure(\Finder\Builder, mixed): mixed|null  $default
     * @return $this
     */
    public function when(mixed $value, \Closure $callback, ?\Closure $default = null): static
    {
        if ($value) {
            return $callback($this, $value) ?: $this;
        }
        if ($default) {
            return $default($this, $value) ?: $this;
        }

        return $this;
    }

    /**
     * @param  \Closure(\Finder\Builder, bool): mixed  $callback
     */
    public function tap(\Closure $callback): static
    {
        return $this->when(true, $callback);
    }

    /**
     * Set the callback that should have an opportunity to modify the database query.
     *
     * @return $this
     */
    public function query(callable $callback): static
    {
        $this->queryCallback = $callback;

        return $this;
    }

    public function raw(): mixed
    {
        return $this->engine()->search($this);
    }

    /**
     * @return \Illuminate\Support\Collection<int, string|null>
     */
    public function keys(): Collection
    {
        return $this->engine()->keys($this);
    }

    public function first(): ?GloballySearchable
    {
        return $this->get()->first();
    }

    /**
     * @return \Illuminate\Support\Collection<int, \Illuminate\Database\Eloquent\Model&\Finder\GloballySearchable>
     */
    public function get(): Collection
    {
        return $this->engine()->get($this);
    }

    /**
     * @return \Illuminate\Contracts\Pagination\Paginator<\Illuminate\Database\Eloquent\Model>
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function simplePaginate(?int $perPage = null, string $pageName = 'page', ?int $page = null): \Illuminate\Contracts\Pagination\Paginator
    {
        $engine = $this->engine();

        $page = $page ?: Paginator::resolveCurrentPage($pageName);

        $perPage = $perPage ?? config('finder.per_page');

        $results = Finder::newCollection($engine->map(
            $this, $rawResults = $engine->paginate($this, $perPage, $page)
        )->all());

        $paginator = Container::getInstance()->makeWith(Paginator::class, [
            'items' => $results,
            'perPage' => $perPage,
            'currentPage' => $page,
            'options' => [
                'path' => Paginator::resolveCurrentPath(),
                'pageName' => $pageName,
            ],
        ])->hasMorePagesWhen(($perPage * $page) < $engine->getTotalCount($rawResults));

        return $paginator->appends('query', $this->query);
    }

    /**
     * @return \Illuminate\Contracts\Pagination\Paginator<\Illuminate\Database\Eloquent\Model>
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function simplePaginateRaw(?int $perPage = null, string $pageName = 'page', ?int $page = null): \Illuminate\Contracts\Pagination\Paginator
    {
        $engine = $this->engine();

        $page = $page ?: Paginator::resolveCurrentPage($pageName);

        $perPage = $perPage ?? config('finder.per_page');

        $results = $engine->paginate($this, $perPage, $page);

        $paginator = Container::getInstance()->makeWith(Paginator::class, [
            'items' => $results,
            'perPage' => $perPage,
            'currentPage' => $page,
            'options' => [
                'path' => Paginator::resolveCurrentPath(),
                'pageName' => $pageName,
            ],
        ])->hasMorePagesWhen(($perPage * $page) < $engine->getTotalCount($results));

        return $paginator->appends('query', $this->query);
    }

    /**
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator<\Illuminate\Database\Eloquent\Model>
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function paginate(?int $perPage = null, string $pageName = 'page', ?int $page = null): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $engine = $this->engine();

        $page = $page ?: Paginator::resolveCurrentPage($pageName);

        $perPage = $perPage ?? config('finder.per_page');

        $results = Finder::newCollection($engine->map(
            $this, $rawResults = $engine->paginate($this, $perPage, $page)
        )->all());

        return Container::getInstance()->makeWith(LengthAwarePaginator::class, [
            'items' => $results,
            'total' => $this->getTotalCount($rawResults),
            'perPage' => $perPage,
            'currentPage' => $page,
            'options' => [
                'path' => Paginator::resolveCurrentPath(),
                'pageName' => $pageName,
            ],
        ])->appends('query', $this->query);
    }

    public function cursorPaginate(int $perPage, ?array $cursor = null): PaginationResult
    {
        $engine = $this->engine();

        $meta = [
            'hasPrevious' => false,
            'hasNext' => false,
        ];

        // Add one to the page, so we can see if there are more.
        /** @var \Elastic\Adapter\Search\SearchResult $rawResults */
        $rawResults = $engine->cursorPaginate($this, $perPage + 1, $cursor);

        /** @var \Illuminate\Support\Collection<int, \Illuminate\Database\Eloquent\Model> $results */
        $results = Finder::newCollection($engine->map(
            $this, $rawResults
        )->all());

        if ($rawResults->hits()->count() > $perPage) {
            $results = $results->slice(0, $perPage);
            $meta['hasNext'] = true;
        }

        $meta['previousCursor'] = $results->first()?->getAttribute('cursor');
        $meta['nextCursor'] = $results->last()?->getAttribute('cursor');

        if ($cursor) {
            $meta['hasPrevious'] = true;
        }

        return new PaginationResult($results, $meta, $rawResults->total() ?? 0);
    }

    /**
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator<\Illuminate\Database\Eloquent\Model>
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function paginateRaw(?int $perPage = null, string $pageName = 'page', ?int $page = null): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $engine = $this->engine();

        $page = $page ?: Paginator::resolveCurrentPage($pageName);

        $perPage = $perPage ?? config('finder.per_page');

        $results = $engine->paginate($this, $perPage, $page);

        return Container::getInstance()->makeWith(LengthAwarePaginator::class, [
            'items' => $results,
            'total' => $this->getTotalCount($results),
            'perPage' => $perPage,
            'currentPage' => $page,
            'options' => [
                'path' => Paginator::resolveCurrentPath(),
                'pageName' => $pageName,
            ],
        ])->appends('query', $this->query);
    }

    public function using(Engine $engine): void
    {
        $this->engine = $engine;
    }

    protected function getTotalCount(mixed $results): ?int
    {
        $engine = $this->engine();

        return $engine->getTotalCount($results);
    }

    protected function engine(): Engine
    {
        return $this->engine ?? app(EngineManager::class)->engine();
    }
}
