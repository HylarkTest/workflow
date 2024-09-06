<?php

declare(strict_types=1);

namespace App\Core\Mappings\Features;

use App\Models\Mapping;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use LaravelUtils\Database\Eloquent\Contracts\AttributeCollection;
use LaravelUtils\Database\Eloquent\Contracts\AttributeCollectionItem;

/**
 * @extends \Illuminate\Support\Collection<int, \App\Core\Mappings\Features\Feature>
 *
 * @implements \LaravelUtils\Database\Eloquent\Contracts\AttributeCollection<int, \App\Core\Mappings\Features\Feature>
 */
class FeatureCollection extends Collection implements AttributeCollection
{
    /**
     * @param  array|\LaravelUtils\Database\Eloquent\Contracts\AttributeCollection<int, \App\Core\Mappings\Features\Feature>  $items
     * @param  \App\Models\Mapping  $model
     */
    public static function makeFromAttribute($items, Model $model): self
    {
        if ($items instanceof static) {
            return $items;
        }
        $features = [];

        foreach ((array) $items as $item) {
            $type = $item['val'] instanceof MappingFeatureType ?
                $item['val'] :
                MappingFeatureType::from($item['val']);

            $features[] = $type->newFeature($model, $item['options'] ?? null);
        }

        return new self($features);
    }

    public function findIndex(string|MappingFeatureType $type): bool|int
    {
        $type = $type instanceof MappingFeatureType ? $type : MappingFeatureType::from($type);

        return $this->search(
            fn (Feature $existingFeature): bool => $existingFeature->type() === $type
        );
    }

    /**
     * @param  \App\Core\Mappings\Features\MappingFeatureType|string  $id
     */
    public function find($id): ?AttributeCollectionItem
    {
        $id = $id instanceof MappingFeatureType ? $id : MappingFeatureType::from($id);

        return $this->first(fn (Feature $item): bool => $item->type() === $id);
    }

    public function instantiateFeature(Mapping $mapping, MappingFeatureType|string $type, array $options): Feature
    {
        $type = $type instanceof MappingFeatureType ? $type : MappingFeatureType::from($type);

        return $type->newFeature($mapping, $options);
    }

    /**
     * @param  \App\Models\Mapping  $model
     */
    public function addItem(array $args, Model $model): AttributeCollectionItem
    {
        $feature = $this->instantiateFeature($model, $args['val'], $args['options']);
        $this->push($feature);

        return $feature;
    }

    /**
     * @param  \App\Core\Mappings\Features\MappingFeatureType|string  $id
     * @param  \App\Models\Mapping  $model
     */
    public function changeItem($id, array $args, Model $model): ?AttributeCollectionItem
    {
        $index = $this->findIndex($id);

        if (\is_bool($index)) {
            return null;
        }

        $feature = $this->instantiateFeature($model, $id, $args);
        array_splice($this->items, $index, 1, [$feature]);

        return $feature;
    }

    /**
     * @param  string  $id
     */
    public function removeItem($id, Model $model): ?AttributeCollectionItem
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
}
