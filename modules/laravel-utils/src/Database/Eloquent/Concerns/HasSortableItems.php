<?php

declare(strict_types=1);

namespace LaravelUtils\Database\Eloquent\Concerns;

trait HasSortableItems
{
    /**
     * @param  array<int, int|string>|\ArrayAccess<int, int|string>  $orders
     */
    public function updateOrder(array|\ArrayAccess $orders): void
    {
        if ($this->isEmpty()) {
            return;
        }

        /** @var \LaravelUtils\Database\Eloquent\Contracts\Sortable<\Illuminate\Database\Eloquent\Model> $model */
        $model = $this->first();
        $model::setNewOrder($orders);
    }
}
