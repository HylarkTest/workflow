<?php

declare(strict_types=1);

namespace Finder;

use Finder\Engines\Engine;
use Illuminate\Database\Eloquent\Collection;

/**
 * @mixin \Illuminate\Database\Eloquent\Model
 *
 * @method static bool usesSoftDelete();
 */
interface GloballySearchable
{
    /**
     * @param  \Illuminate\Database\Eloquent\Collection<int, \Illuminate\Database\Eloquent\Model&\Finder\GloballySearchable>  $models
     */
    public function queueMakeGloballySearchable(Collection $models): void;

    /**
     * Dispatch the job to make the given models unsearchable.
     *
     * @param  \Illuminate\Database\Eloquent\Collection<int, \Illuminate\Database\Eloquent\Model&\Finder\GloballySearchable>  $models
     */
    public function queueRemoveFromGlobalSearch(Collection $models): void;

    public function shouldBeGloballySearchable(): bool;

    public function globalSearchIndexShouldBeUpdated(): bool;

    public static function makeAllGloballySearchable(?int $chunk = null): void;

    public function globallySearchable(): void;

    /** @phpstan-ignore-next-line  */
    public function shardRouting();

    public static function removeAllFromGlobalSearch(): void;

    public function globallyUnsearchable(): void;

    public function wasGloballySearchableBeforeUpdate(): bool;

    public function wasGloballySearchableBeforeDelete(): bool;

    public static function enableGlobalSearchSyncing(): void;

    public static function disableGlobalSearchSyncing(): void;

    /**
     * @template T
     *
     * @param  callable(): T  $callback
     * @return T
     */
    public static function withoutSyncingToGlobalSearch(callable $callback);

    public function toGloballySearchableArray(): array;

    /**
     * @return string|array<(callable(\Illuminate\Database\Eloquent\Builder<\Illuminate\Database\Eloquent\Model>): mixed)|string>
     */
    public function globallySearchableWith(): array|string;

    public function globallySearchableUsing(): Engine;

    public function syncWithGlobalSearchUsing(): string;

    public function syncWithGlobalSearchUsingQueue(): ?string;

    public function pushSoftDeleteFinderMetadata(): static;

    public function finderMetadata(): array;

    public function withFinderMetadata(string $key, mixed $value): static;

    public function getFinderKey(): string;

    public function getFinderKeyName(): string;

    public function globallySearchableAs(): string;

    public function finderTypename(): string;
}
