<?php

declare(strict_types=1);

namespace App\Core\Mappings\Repositories;

use App\Models\Item;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use App\Core\Mappings\ItemDuplicator;
use Lampager\Laravel\PaginationResult;
use GraphQL\Executor\Promise\Adapter\SyncPromise;
use App\Core\Mappings\Features\MappingFeatureType;

class ItemRepository
{
    /**
     * @param  PaginationArgs  $paginationArgs
     * @param  OrderBy  $orderBy
     */
    public function paginateItems(array $paginationArgs, ItemFilter $filter, array $orderBy = [], ?string $group = null): PaginationResult|array|SyncPromise
    {
        if ($this->shouldUseEs($filter, $group, $orderBy)) {
            return (new ElasticsearchItemRepository)->readPage($paginationArgs, $filter, $orderBy, $group);
        }

        return (new EloquentItemRepository)->readPage($paginationArgs, $filter, $orderBy, $group);
    }

    protected function shouldUseEs(ItemFilter $filter, ?string $group, array $orderBy): bool
    {
        if ($group && Str::startsWith($group, 'field:')) {
            return true;
        }

        if (Arr::first($orderBy, fn ($order) => Str::startsWith($order['field'], 'field:'))) {
            return true;
        }

        return $filter->getFilters()
            || $filter->getMarkers()
            || $filter->getFields();
    }

    public function duplicateItem(Item $item, array $args): Item
    {
        $duplicator = new ItemDuplicator($item);
        if ($args['withMarkers'] ?? false) {
            $duplicator->withMarkers();
        }
        if ($args['withRelationships'] ?? false) {
            $duplicator->withRelationships();
        }
        if ($args['withAssignees'] ?? false) {
            $duplicator->withAssignees();
        }
        if ($args['withFeaturesTimekeeper'] ?? false) {
            $duplicator->withFeatures([MappingFeatureType::TIMEKEEPER]);
        }
        $featuresToClone = collect([
            'withFeaturesTodos' => MappingFeatureType::TODOS,
            'withFeaturesEvents' => MappingFeatureType::EVENTS,
            'withFeaturesDocuments' => MappingFeatureType::DOCUMENTS,
            'withFeaturesNotes' => MappingFeatureType::NOTES,
            'withFeaturesLinks' => MappingFeatureType::LINKS,
            'withFeaturesPins' => MappingFeatureType::PINBOARD,
        ])->filter(fn ($value, $key) => $args[$key] ?? false)->values();
        $duplicator->cloneFeatures($featuresToClone->all());

        return $duplicator->duplicate();
    }
}
