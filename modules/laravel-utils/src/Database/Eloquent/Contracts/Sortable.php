<?php

declare(strict_types=1);

namespace LaravelUtils\Database\Eloquent\Contracts;

use Illuminate\Database\Eloquent\Builder;

/**
 * @template TModel of \Illuminate\Database\Eloquent\Model
 *
 * @mixin \Illuminate\Database\Eloquent\Model
 */
interface Sortable
{
    /**
     * Modify the order column value.
     */
    public function setHighestOrderNumber(): void;

    /**
     * Let's be nice and provide an ordered scope.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<TModel>  $query
     */
    public function scopeOrdered(Builder $query): void;

    /**
     * This function reorders the records: the record with the first id in the array
     * will get order 1, the record with the second it will get order 2...
     *
     * @param  array<int, int|string>|\ArrayAccess<int, int|string>  $ids
     */
    public static function setNewOrder(array|\ArrayAccess $ids, int $startOrder = 1): void;

    /**
     * Determine if the order column should be set when saving a new model instance.
     */
    public function shouldSortWhenCreating(): bool;

    public function getOrder(): int;

    public function setOrder(int $order): void;

    /**
     * @param  \LaravelUtils\Database\Eloquent\Contracts\Sortable<TModel>  $model
     * @return $this
     */
    public function swapOrderWithModel(self $model): static;

    public function isLastInOrder(): bool;

    public function isFirstInOrder(): bool;

    public function moveBelow(self $model): static;

    public function moveAbove(self $model): static;

    public function moveToPosition(int $referenceOrder): static;

    public function moveToStart(): static;
}
