<?php

declare(strict_types=1);

namespace LaravelUtils\Database\Eloquent\Contracts;

use Illuminate\Support\Enumerable;
use Illuminate\Database\Eloquent\Model;

/**
 * @template TKey of array-key
 * @template TValue of \LaravelUtils\Database\Eloquent\Contracts\AttributeCollectionItem>
 *
 * @extends \Illuminate\Support\Enumerable<TKey, TValue>
 */
interface AttributeCollection extends Enumerable
{
    /**
     * @param  array<TKey, array<string, mixed>|TValue>|\LaravelUtils\Database\Eloquent\Contracts\AttributeCollection<TKey, TValue>  $items
     * @return static<TKey, TValue>
     */
    public static function makeFromAttribute($items, Model $model): self;

    /**
     * @param  mixed  $id
     * @return TValue|null
     */
    public function find($id): ?AttributeCollectionItem;

    /**
     * @return TValue
     */
    public function addItem(array $args, Model $model);

    /**
     * @param  mixed  $id
     * @return TValue|null
     */
    public function changeItem($id, array $args, Model $model);

    /**
     * @param  mixed  $id
     * @return TValue|null
     */
    public function removeItem($id, Model $model);
}
