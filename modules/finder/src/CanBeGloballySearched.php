<?php

declare(strict_types=1);

namespace Finder;

use Finder\Engines\Engine;
use Finder\Core\FinderKeyResolverInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection as BaseCollection;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;

/**
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait CanBeGloballySearched
{
    protected static string $_index;

    /**
     * Additional metadata attributes managed by Finder.
     *
     * @var array<string, mixed>
     */
    protected array $finderMetadata = [];

    /**
     * Boot the trait.
     */
    public static function bootCanBeGloballySearched(): void
    {
        /** @var \Finder\GloballySearchableScope<static> $scope */
        $scope = new GloballySearchableScope;
        static::addGlobalScope($scope);

        static::observe(new ModelObserver);

        /** @phpstan-ignore-next-line Need to use static */
        (new static)->registerGloballySearchableMacros();
    }

    /**
     * Register the searchable macros.
     */
    public function registerGloballySearchableMacros(): void
    {
        $self = $this;

        BaseCollection::macro('globallySearchable', function () use ($self) {
            /** @phpstan-ignore-next-line In context of collection */
            $self->queueMakeGloballySearchable($this);
        });

        BaseCollection::macro('globallyUnsearchable', function () use ($self) {
            /** @phpstan-ignore-next-line In context of collection */
            $self->queueRemoveFromGlobalSearch($this);
        });
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Collection<int, \Illuminate\Database\Eloquent\Model&\Finder\GloballySearchable>  $models
     */
    public function queueMakeGloballySearchable(Collection $models): void
    {
        if ($models->isEmpty()) {
            return;
        }

        if (! config('finder.queue')) {
            $models->first()?->globallySearchableUsing()->update($models);

            return;
        }

        /** @var \Finder\Jobs\MakeGloballySearchable $job */
        $job = new Finder::$makeSearchableJob($models);
        dispatch($job->onQueue($models->first()?->syncWithGlobalSearchUsingQueue())
            ->onConnection($models->first()?->syncWithGlobalSearchUsing()));
    }

    /**
     * Dispatch the job to make the given models unsearchable.
     *
     * @param  \Illuminate\Database\Eloquent\Collection<int, \Illuminate\Database\Eloquent\Model&\Finder\GloballySearchable>  $models
     */
    public function queueRemoveFromGlobalSearch(Collection $models): void
    {
        if ($models->isEmpty()) {
            return;
        }

        if (! config('finder.queue')) {
            $models->first()?->globallySearchableUsing()->delete($models);

            return;
        }

        dispatch(new Finder::$removeFromFinderJob($models))
            ->onQueue($models->first()?->syncWithGlobalSearchUsingQueue())
            ->onConnection($models->first()?->syncWithGlobalSearchUsing());
    }

    public function shouldBeGloballySearchable(): bool
    {
        return true;
    }

    public function globalSearchIndexShouldBeUpdated(): bool
    {
        return true;
    }

    public static function makeAllGloballySearchable(?int $chunk = null): void
    {
        /** @phpstan-ignore-next-line Needs to be static */
        $self = new static;

        $softDelete = static::usesSoftDelete() && config('finder.soft_delete', false);

        tenancy()->runForMultiple(null, function () use ($self, $softDelete, $chunk) {
            /** @phpstan-ignore-next-line  */
            $self->newQuery()
                ->when(true, function ($query) use ($self) {
                    $self->makeAllGloballySearchableUsing($query);
                })
                ->when($softDelete, function ($query) {
                    /** @phpstan-ignore-next-line  */
                    $query->withTrashed();
                })
                ->orderBy($self->getKeyName())
                ->globallySearchable($chunk);
        });
    }

    public function globallySearchable(): void
    {
        $this->newCollection([$this])->globallySearchable();
    }

    public function shardRouting(): string|int|null
    {
        return null;
    }

    public static function removeAllFromGlobalSearch(): void
    {
        /** @phpstan-ignore-next-line Needs to be static */
        $self = new static;

        $self->globallySearchableUsing()->flush($self);
    }

    public function globallyUnsearchable(): void
    {
        $this->newCollection([$this])->globallyUnsearchable();
    }

    public function wasGloballySearchableBeforeUpdate(): bool
    {
        return true;
    }

    public function wasGloballySearchableBeforeDelete(): bool
    {
        return true;
    }

    public static function enableGlobalSearchSyncing(): void
    {
        ModelObserver::enableSyncingFor(static::class);
    }

    public static function disableGlobalSearchSyncing(): void
    {
        ModelObserver::disableSyncingFor(static::class);
    }

    /**
     * @template T
     *
     * @param  callable(): T  $callback
     * @return T
     */
    public static function withoutSyncingToGlobalSearch(callable $callback)
    {
        static::disableGlobalSearchSyncing();

        try {
            return $callback();
        } finally {
            static::enableGlobalSearchSyncing();
        }
    }

    /**
     * @return array{primary: string|array<string>, secondary?: string|array<string>}
     */
    public function toGloballySearchableArray(): array
    {
        throw new \Exception('The `toGlobalSearchableArray` must be overridden to match the structure of the index');
    }

    /**
     * @return array<(callable(\Illuminate\Database\Eloquent\Builder<\Illuminate\Database\Eloquent\Model>): mixed)|string>|string
     */
    public function globallySearchableWith(): array|string
    {
        return [];
    }

    public function globallySearchableUsing(): Engine
    {
        return app(EngineManager::class)->engine();
    }

    public function syncWithGlobalSearchUsing(): string
    {
        return config('finder.queue.connection') ?: config('queue.default');
    }

    public function syncWithGlobalSearchUsingQueue(): ?string
    {
        /** @phpstan-ignore-next-line This should always be a string */
        return config('finder.queue.queue');
    }

    public function pushSoftDeleteFinderMetadata(): static
    {
        $isTrashed = method_exists($this, 'trashed') && $this->trashed();

        return $this->withFinderMetadata('__soft_deleted', $isTrashed ? 1 : 0);
    }

    /**
     * @return array<string, mixed>
     */
    public function finderMetadata(): array
    {
        return $this->finderMetadata;
    }

    public function withFinderMetadata(string $key, mixed $value): static
    {
        $this->finderMetadata[$key] = $value;

        return $this;
    }

    public function getFinderKey(): string
    {
        return app(FinderKeyResolverInterface::class)->generateKey($this, $this->globallySearchableAs());
    }

    public function getFinderKeyName(): string
    {
        return $this->getQualifiedKeyName();
    }

    /**
     * @throws \Exception
     */
    public function globallySearchableAs(): string
    {
        if (isset(static::$_index)) {
            return static::$_index;
        }
        foreach (config('finder.models') as $index => $classList) {
            if (\in_array(static::class, $classList, true)) {
                static::$_index = config('finder.prefix').$index;

                return static::$_index;
            }
        }

        throw new \Exception('Could not find an index for class '.static::class);
    }

    public function finderTypename(): string
    {
        return class_basename($this);
    }

    public static function usesSoftDelete(): bool
    {
        return \in_array(SoftDeletes::class, class_uses_recursive(static::class), true);
    }

    /**
     * Modify the query used to retrieve models when making all of the models searchable.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<static>  $query
     * @return \Illuminate\Database\Eloquent\Builder<static>
     */
    protected function makeAllGloballySearchableUsing(EloquentBuilder $query): EloquentBuilder
    {
        return $query;
    }
}
