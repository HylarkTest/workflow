<?php

declare(strict_types=1);

namespace LaravelUtils\Database\Eloquent\Contracts;

interface SortableCollection
{
    /**
     * @param  array<int, int|string>|\ArrayAccess<int, int|string>  $orders
     */
    public function updateOrder(array|\ArrayAccess $orders): void;
}
