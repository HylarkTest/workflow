<?php

declare(strict_types=1);

namespace App\Core\Mappings\Markers;

use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Model;
use Mappings\Core\Mappings\Concerns\HasUniqueNames;
use Mappings\Core\Mappings\Relationships\Relationship;
use LaravelUtils\Database\Eloquent\AttributeCollection;
use LaravelUtils\Database\Eloquent\Contracts\AttributeCollectionItem;

/**
 * @extends \LaravelUtils\Database\Eloquent\AttributeCollection<int, \App\Core\Mappings\Markers\MappingMarkerGroup>
 */
class MappingMarkerGroupCollection extends AttributeCollection
{
    use HasUniqueNames;

    /**
     * @return \App\Core\Mappings\Markers\MappingMarkerGroup
     */
    public function addItem(array $args, Model $model): AttributeCollectionItem
    {
        $mappingMarkerGroup = new MappingMarkerGroup($args);
        $apiName = $mappingMarkerGroup->apiName;

        $mappingMarkerGroup->apiName = $this->getUniqueName($apiName);

        $this->push($mappingMarkerGroup);

        return $mappingMarkerGroup;
    }

    /**
     * @param  int|string  $id
     * @return \App\Core\Mappings\Markers\MappingMarkerGroup|null
     */
    public function changeItem($id, array $args, Model $model): ?AttributeCollectionItem
    {
        /** @var \App\Core\Mappings\Markers\MappingMarkerGroup $originalGroup */
        $originalGroup = $this->find($id);
        $originalArray = $originalGroup->toArray();
        $originalGroup->name = $args['name'] ?? $originalGroup->name;
        $originalGroup->apiName = $args['apiName'] ?? $originalGroup->apiName;
        $originalGroup->type = $args['type'] ?? $originalGroup->type;
        $updatedArray = $originalGroup->toArray();

        if ($originalArray !== $updatedArray) {
            $originalGroup->updatedAt = (string) Carbon::now();
        }

        return $originalGroup;
    }

    /**
     * @param  int|string  $id
     * @return \App\Core\Mappings\Markers\MappingMarkerGroup|null
     */
    public function removeItem($id, Model $model): ?AttributeCollectionItem
    {
        $originalKey = $this->search(fn (MappingMarkerGroup $item) => $item->group === $id || $item->id() === $id);

        if (\is_bool($originalKey)) {
            return null;
        }

        $item = $this[$originalKey];

        $this->forget($originalKey);
        $this->items = array_values($this->items);

        return $item;
    }

    /**
     * @param  array|static  $items
     * @param  \App\Models\Mapping  $model
     */
    public static function makeFromAttribute($items, Model $model): self
    {
        if ($items instanceof static) {
            return $items;
        }

        return new self(array_map(
            static function (array $options) use ($model): MappingMarkerGroup {
                if ($relationship = $options['relationship'] ?? false) {
                    /** @var Relationship $relationship */
                    $relationship = $model->relationships->find($relationship);
                    $options['relationship'] = $relationship;
                }

                return new MappingMarkerGroup($options);
            },
            (array) $items,
        ));
    }
}
