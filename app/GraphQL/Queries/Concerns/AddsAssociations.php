<?php

declare(strict_types=1);

namespace App\GraphQL\Queries\Concerns;

use App\Models\Base;
use App\Models\Item;
use App\Core\Mappings\Features\MappingFeatureType;
use Illuminate\Database\Eloquent\ModelNotFoundException;

trait AddsAssociations
{
    /**
     * @return \App\Models\Item[]
     */
    protected function getAssociatedItems(Base $base, array $args, MappingFeatureType $type): array
    {
        $items = collect();
        if (isset($args['input']['associations'])) {
            /** @var \Illuminate\Database\Eloquent\Collection<int, \App\Models\Item> $items */
            $items = $base->items()
                ->findOrFail($args['input']['associations']);
            $notAllowedItems = $items->filter(fn (Item $item) => ! $item->mapping->featureEnabled($type));
            if ($notAllowedItems->isNotEmpty()) {
                throw (new ModelNotFoundException)->setModel(Item::class, $notAllowedItems->modelKeys());
            }
        }

        return $items->all();
    }
}
