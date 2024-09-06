<?php

declare(strict_types=1);

namespace LaravelUtils\Database\Eloquent;

use Illuminate\Support\Collection;
use LaravelUtils\Database\Eloquent\Contracts\AttributeCollectionItem;
use LaravelUtils\Database\Eloquent\Contracts\AttributeCollection as AttributeCollectionInterface;

/**
 * @template TKey of array-key
 * @template TValue of \LaravelUtils\Database\Eloquent\Contracts\AttributeCollectionItem>
 *
 * @extends \Illuminate\Support\Collection<TKey, TValue>
 *
 * @implements \LaravelUtils\Database\Eloquent\Contracts\AttributeCollection<TKey, TValue>
 */
abstract class AttributeCollection extends Collection implements AttributeCollectionInterface
{
    public function find($id): ?AttributeCollectionItem
    {
        return $this->first(fn (AttributeCollectionItem $item) => $this->matchId($item->id(), $id));
    }

    /**
     * @return int|string|bool
     */
    public function findIndex(string $id)
    {
        return $this->search(fn (AttributeCollectionItem $item) => $this->matchId($item->id(), $id));
    }

    /**
     * @param  mixed  $id
     * @return TValue|null
     */
    public function forgetItem($id)
    {
        $originalKey = $this->findIndex($id);

        if (\is_bool($originalKey)) {
            return null;
        }

        $item = $this[$originalKey];

        $this->forget($originalKey);
        $this->items = array_values($this->items);

        return $item;
    }

    protected function matchId(string|int $id1, string|int $id2): bool
    {
        return $id1 === $id2;
    }
}
