<?php

declare(strict_types=1);

namespace Finder;

use Finder\Jobs\RemoveFromFinder;
use Illuminate\Support\Collection;
use Finder\Jobs\MakeGloballySearchable;

class Finder
{
    /**
     * The job class that should make models searchable.
     *
     * @var class-string
     */
    public static string $makeSearchableJob = MakeGloballySearchable::class;

    /**
     * The job that should remove models from the search index.
     *
     * @var class-string
     */
    public static $removeFromFinderJob = RemoveFromFinder::class;

    /**
     * Specify the job class that should make models searchable.
     *
     * @param  class-string  $class
     */
    public static function makeGloballySearchableUsing(string $class): void
    {
        static::$makeSearchableJob = $class;
    }

    /**
     * Specify the job class that should remove models from the search index.
     *
     * @param  class-string  $class
     */
    public static function removeFromFinderUsing(string $class): void
    {
        static::$removeFromFinderJob = $class;
    }

    public static function search(?string $query = null, ?string $index = null, ?\Closure $callback = null): Builder
    {
        $index = $index ?? config('finder.prefix').config('finder.index');

        return app(Builder::class, [
            'index' => $index,
            'query' => $query,
            'callback' => $callback,
            'softDelete' => config('finder.soft_delete', false),
        ]);
    }

    public static function index(string $index): Builder
    {
        return static::search(null, $index);
    }

    /**
     * @param  array<int, \Illuminate\Database\Eloquent\Model&\Finder\GloballySearchable>  $items
     * @return \Illuminate\Support\Collection<int, \Illuminate\Database\Eloquent\Model&\Finder\GloballySearchable>
     */
    public static function newCollection(array $items = []): Collection
    {
        /** @var class-string<\Illuminate\Support\Collection<int, \Illuminate\Database\Eloquent\Model&\Finder\GloballySearchable>> $class */
        $class = config('finder.collection');

        return new $class($items);
    }
}
