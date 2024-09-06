<?php

declare(strict_types=1);

namespace LaravelUtils\Database\Eloquent\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use LaravelUtils\Database\Eloquent\Contracts\Sortable;
use LaravelUtils\Database\Eloquent\Collections\SortableCollection;
use LaravelUtils\Database\Eloquent\Scopes\DefaultOrderIfNotOrderedScope;

/**
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait IsSortable
{
    protected string $orderColumn = 'order';

    protected bool $shouldSortWhenCreating = true;

    /**
     * Always sort by the order column if the query doesn't include an explicit
     * order clause.
     */
    protected static bool $sortIfNotExplicitlySorting = true;

    /**
     * Always sort by the order column if the query doesn't include an explicit
     * order clause.
     */
    protected string $defaultSortOrder = 'asc';

    /**
     * Create a new Eloquent Collection instance.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function newCollection(array $models = [])
    {
        return new SortableCollection($models);
    }

    public static function bootIsSortable(): void
    {
        static::creating(function (Sortable $model) {
            if ($model->shouldSortWhenCreating() && ! $model->getAttribute($model->orderColumnName())) {
                $model->setHighestOrderNumber();
            }
        });

        if (static::$sortIfNotExplicitlySorting) {
            $model = new static;
            self::addGlobalScope(new DefaultOrderIfNotOrderedScope(
                $model->qualifyColumn($model->orderColumnName()),
                $model->defaultSortOrder,
            ));
        }
    }

    public function setHighestOrderNumber(): void
    {
        $this->setAttribute($this->orderColumnName(), $this->getHighestOrderNumber() + 1);
    }

    public function orderColumnName(): string
    {
        return $this->orderColumn;
    }

    public function getOrder(): int
    {
        return (int) $this->getAttribute($this->orderColumnName());
    }

    public function setOrder(int $order): void
    {
        $this->setAttribute($this->orderColumnName(), $order);
    }

    public function shouldSortWhenCreating(): bool
    {
        return $this->shouldSortWhenCreating;
    }

    public function getHighestOrderNumber(): int
    {
        return (int) $this->buildSortQuery()->max($this->qualifyColumn($this->orderColumnName()));
    }

    public function getLowestOrderNumber(): int
    {
        return (int) $this->buildSortQuery()->min($this->qualifyColumn($this->orderColumnName()));
    }

    public function scopeOrdered(Builder $query, ?string $direction = null): void
    {
        $direction = $direction ?? $this->defaultSortOrder;
        $query->orderBy($this->qualifyColumn($this->orderColumnName()), $direction);
    }

    public static function setNewOrder(array|\ArrayAccess $ids, int $startOrder = 1, ?string $primaryKeyColumn = null): void
    {
        $model = new static;

        $orderColumn = $model->orderColumnName();

        $primaryKeyColumn = $primaryKeyColumn ?? $model->getKeyName();

        foreach ($ids as $id) {
            static::withoutGlobalScope(SoftDeletingScope::class)
                ->where($primaryKeyColumn, $id)
                ->update([$orderColumn => $startOrder++]);
        }
    }

    public static function setNewOrderByCustomColumn(string $primaryKeyColumn, array|\ArrayAccess $ids, int $startOrder = 1): void
    {
        self::setNewOrder($ids, $startOrder, $primaryKeyColumn);
    }

    public function moveOrderDown(): static
    {
        $orderColumnName = $this->orderColumnName();

        $swapWithModel = $this->buildSortQuery()->limit(1)
            ->ordered()
            ->where($orderColumnName, '>', $this->getOrder())
            ->first();

        if (! $swapWithModel) {
            return $this;
        }

        return $this->swapOrderWithModel($swapWithModel);
    }

    public function moveOrderUp(): static
    {
        $orderColumnName = $this->orderColumnName();

        $swapWithModel = $this->buildSortQuery()->limit(1)
            ->ordered('desc')
            ->where($orderColumnName, '<', $this->getOrder())
            ->first();

        if (! $swapWithModel) {
            return $this;
        }

        return $this->swapOrderWithModel($swapWithModel);
    }

    public function swapOrderWithModel(Sortable $otherModel): static
    {
        $oldOrderOfOtherModel = $otherModel->getOrder();

        $otherModel->setOrder($this->getOrder());
        $otherModel->save();

        $this->setOrder($oldOrderOfOtherModel);
        $this->save();

        return $this;
    }

    public static function swapOrder(Sortable $model, Sortable $otherModel): void
    {
        $model->swapOrderWithModel($otherModel);
    }

    public function moveToStart(): static
    {
        /** @var \LaravelUtils\Database\Eloquent\Contracts\Sortable $firstModel */
        $firstModel = $this->buildSortQuery()->limit(1)
            ->ordered()
            ->first();

        if ($firstModel->getKey() === $this->getKey()) {
            return $this;
        }

        $orderColumnName = $this->orderColumnName();

        $oldOrder = $this->getOrder();

        $this->setOrder($firstModel->getOrder());
        $this->save();

        $this->buildSortQuery()
            ->where($this->getKeyName(), '!=', $this->getKey())
            ->where($orderColumnName, '<', $oldOrder)
            ->increment($orderColumnName);

        return $this;
    }

    public function moveToEnd(): static
    {
        $maxOrder = $this->getHighestOrderNumber();

        $orderColumnName = $this->orderColumnName();

        if ($this->getAttribute($orderColumnName) === $maxOrder) {
            return $this;
        }

        $oldOrder = $this->getOrder();

        $this->setAttribute($orderColumnName, $maxOrder);
        $this->save();

        $this->buildSortQuery()->where($this->getKeyName(), '!=', $this->getKey())
            ->where($orderColumnName, '>', $oldOrder)
            ->decrement($orderColumnName);

        return $this;
    }

    public function moveBelow(Sortable $model): static
    {
        if ($model->getKey() === $this->getKey()) {
            return $this;
        }

        return $this->moveToPosition($model->getOrder());
    }

    public function moveAbove(Sortable $model): static
    {
        if ($model->getKey() === $this->getKey()) {
            return $this;
        }

        return $this->moveToPosition($model->getOrder() - 1);
    }

    public function moveToPosition(int $referenceOrder): static
    {
        if ($this->getOrder() === $referenceOrder) {
            return $this;
        }

        $orderColumnName = $this->orderColumnName();

        $originalOrder = $this->getOrder();

        if ($originalOrder > $referenceOrder) {
            $this->buildSortQuery()
                ->whereBetween($orderColumnName, [$referenceOrder + 1, $originalOrder - 1])
                ->increment($orderColumnName);
            $this->setOrder($referenceOrder + 1);
        } else {
            $this->buildSortQuery()
                ->whereBetween($orderColumnName, [$originalOrder + 1, $referenceOrder])
                ->decrement($orderColumnName);
            $this->setOrder($referenceOrder);
        }

        $this->save();

        return $this;
    }

    public function isLastInOrder(): bool
    {
        return $this->getOrder() === $this->getHighestOrderNumber();
    }

    public function isFirstInOrder(): bool
    {
        return $this->getOrder() === $this->getLowestOrderNumber();
    }

    public function buildSortQuery(): Builder
    {
        return static::query();
    }
}
