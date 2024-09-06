<?php

declare(strict_types=1);

namespace App\GraphQL\Queries\Concerns;

use App\Models\Item;
use Illuminate\Database\Eloquent\Model;
use App\Models\Contracts\FeatureListItem;

/**
 * @template TList of \App\Models\Contracts\FeatureList
 * @template TItem of \App\Models\Contracts\FeatureListItem
 */
trait BroadcastsChanges
{
    /**
     * @use \App\GraphQL\Queries\Concerns\BroadcastsFeatureItemChanges<TList, TItem>
     */
    use BroadcastsFeatureItemChanges;

    use BroadcastsItemChanges;

    /**
     * @param  \App\Models\Item|\App\Models\Contracts\FeatureListItem<TList, TItem>|\Illuminate\Database\Eloquent\Model  $item
     */
    protected function broadcastChanges(Item|FeatureListItem|Model $item): void
    {
        if ($item instanceof Item) {
            $this->broadcastItemChange($item);
        }
        if ($item instanceof FeatureListItem) {
            $this->broadcastFeatureItemChange($item);
        }
    }
}
