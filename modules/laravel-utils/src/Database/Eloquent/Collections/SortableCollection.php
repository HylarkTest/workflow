<?php

declare(strict_types=1);

namespace LaravelUtils\Database\Eloquent\Collections;

use Illuminate\Database\Eloquent\Collection;
use LaravelUtils\Database\Eloquent\Concerns\HasSortableItems;
use LaravelUtils\Database\Eloquent\Contracts\SortableCollection as SortableCollectionContract;

/**
 * @template TKey of array-key
 * @template TValue of \Illuminate\Database\Eloquent\Model
 *
 * @extends \Illuminate\Database\Eloquent\Collection<TKey, TValue>
 */
class SortableCollection extends Collection implements SortableCollectionContract
{
    use HasSortableItems;
}
