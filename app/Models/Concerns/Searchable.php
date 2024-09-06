<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use App\Models\Contracts\NotScoped;
use Elastic\ScoutDriverPlus\Factories\ParameterFactory;
use Elastic\ScoutDriverPlus\Searchable as BaseSearchable;
use Elastic\ScoutDriverPlus\Builders\SearchParametersBuilder;

trait Searchable
{
    use BaseSearchable;

    /**
     * @param  \Closure|\Elastic\ScoutDriverPlus\Builders\QueryBuilderInterface|array|null  $query
     */
    public static function searchQuery($query = null): SearchParametersBuilder
    {
        /** @phpstan-ignore-next-line  */
        if (! is_a(static::class, NotScoped::class)) {
            $base = tenancy()->tenant;
            if (! $base) {
                throw new \Exception('Cannot query without a tenant');
            }

            if (isset($query)) {
                $query = ParameterFactory::makeQuery($query);
                if (! isset($query['bool'])) {
                    $query['bool'] = [];
                }
                if (! isset($query['bool']['filter'])) {
                    $query['bool']['filter'] = [];
                }

                $query['bool']['filter'][] = ['term' => ['_routing' => $base->getKey()]];
            }
        }

        $builder = new SearchParametersBuilder(new self);

        if ($query) {
            $builder->query($query);
        }

        $builder->preference((string) $base->getKey());

        return $builder;
    }

    public function shardRouting(): string|int|null
    {
        return null;
    }

    /**
     * @template T
     *
     * @param  \Closure(): T  $cb
     * @return T
     */
    public static function instantSync(\Closure $cb): mixed
    {
        $originalQueue = config('scout.queue');
        $originalRefresh = config('elastic.scout_driver.refresh_documents');
        config([
            'scout.queue' => false,
            'elastic.scout_driver.refresh_documents' => true,
        ]);
        $result = $cb();
        config([
            'scout.queue' => $originalQueue,
            'elastic.scout_driver.refresh_documents' => $originalRefresh,
        ]);

        return $result;
    }

    public function updateAndSyncWithSearch(array $attributes = [], array $options = []): bool
    {
        return static::instantSync(fn () => $this->update($attributes, $options));
    }

    public function instantSearchable(): void
    {
        static::instantSync(fn () => $this->searchable());
    }
}
