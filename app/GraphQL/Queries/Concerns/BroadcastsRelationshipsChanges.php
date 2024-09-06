<?php

declare(strict_types=1);

namespace App\GraphQL\Queries\Concerns;

use App\Models\Item;
use Illuminate\Database\Eloquent\Collection;

trait BroadcastsRelationshipsChanges
{
    use BroadcastsItemChanges;

    /**
     * @param  Item|Collection<int, Item>|null  $relatedItems
     */
    public function broadcastRelationshipChanges(Item $item, Item|Collection|null $relatedItems): void
    {
        $this->broadcastItemChange($item);

        if ($relatedItems === null) {
            return;
        }

        if ($relatedItems instanceof Collection) {
            foreach ($relatedItems as $relatedItem) {
                $this->broadcastItemChange($relatedItem);
            }
        } else {
            $this->broadcastItemChange($relatedItems);
        }
    }

    protected function relationshipMutationResponse(Item $item, string $message = 'Relationship was updated successfully'): array
    {
        return $this->mutationResponse(
            200,
            $message,
            [$item->mapping->api_singular_name => $item]
        );
    }
}
