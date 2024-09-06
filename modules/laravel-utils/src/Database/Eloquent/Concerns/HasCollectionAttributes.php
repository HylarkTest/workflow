<?php

declare(strict_types=1);

namespace LaravelUtils\Database\Eloquent\Concerns;

use Mappings\Events\AttributeItemAdded;
use Mappings\Events\AttributeItemAdding;
use Mappings\Events\AttributeItemChanged;
use Mappings\Events\AttributeItemRemoved;
use Mappings\Events\AttributeItemChanging;
use Mappings\Events\AttributeItemRemoving;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use LaravelUtils\Database\Eloquent\Contracts\AttributeCollection;
use LaravelUtils\Database\Eloquent\Contracts\AttributeCollectionItem;

/**
 * Trait HasCollectionAttributes
 *
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait HasCollectionAttributes
{
    /**
     * @var array<string, \LaravelUtils\Database\Eloquent\Contracts\AttributeCollection>
     */
    protected array $resolvedCollections = [];

    /**
     * @return array<string, class-list<\LaravelUtils\Database\Eloquent\Contracts\AttributeCollection>>
     */
    public function getCollectionAttributes(): array
    {
        return $this->collectionAttributes;
    }

    /**
     * @return class-string<\LaravelUtils\Database\Eloquent\Contracts\AttributeCollection>
     */
    public function collectionClass(string $attribute): string
    {
        return $this->getCollectionAttributes()[$attribute];
    }

    public function attributeCollection(string $attribute): AttributeCollection
    {
        if ($collection = ($this->resolvedCollections[$attribute] ?? false)) {
            return $collection;
        }
        $value = $this->getAttribute($attribute);

        return $this->resolvedCollections[$attribute] = $this->getAttributeCollection($attribute, $value);
    }

    /**
     * @param  array|string  $value
     */
    public function getAttributeCollection(string $attribute, $value): AttributeCollection
    {
        return ($this->collectionClass($attribute))::makeFromAttribute(
            \is_string($value) ? $this->fromJson($value) : $value, $this
        );
    }

    public function addToCollection(string $attribute, array $args): AttributeCollectionItem
    {
        $collection = $this->attributeCollection($attribute);
        $item = $collection->addItem($args, $this);

        $this->setAttribute($attribute, $collection);

        return $item;
    }

    public function saveToCollection(string $attribute, array $args): ?AttributeCollectionItem
    {
        $collection = $this->attributeCollection($attribute);
        $item = $collection->addItem($args, $this);

        $this->setAttribute($attribute, $collection);

        $result = $this->fireAttributeEvent(AttributeItemAdding::class, $attribute, $item);

        if ($result === false) {
            return null;
        }

        $this->save();

        $this->fireAttributeEvent(AttributeItemAdded::class, $attribute, $item, false);

        return $item;
    }

    public function changeInCollection(string $attribute, mixed $id, array $args): ?AttributeCollectionItem
    {
        $collection = $this->attributeCollection($attribute);

        $item = $collection->changeItem($id, $args, $this);

        if (! $item) {
            $this->throwItemNotFound($attribute, $id);
        }

        $this->setAttribute($attribute, $collection);

        if ($this->isDirty($attribute)) {
            $result = $this->fireAttributeEvent(AttributeItemChanging::class, $attribute, $item);

            if ($result === false) {
                return null;
            }
            $this->save();
            $this->fireAttributeEvent(AttributeItemChanged::class, $attribute, $item, false);
        }

        return $item;
    }

    public function removeFromCollection(string $attribute, mixed $id): ?AttributeCollectionItem
    {
        $collection = $this->attributeCollection($attribute);

        $item = $collection->find($id);

        if (! $item) {
            $this->throwItemNotFound($attribute, $id);
        }

        $result = $this->fireAttributeEvent(AttributeItemRemoving::class, $attribute, $item);

        if ($result === false) {
            return null;
        }

        $collection->removeItem($id, $this);

        $this->setAttribute($attribute, $collection);
        $this->save();
        $this->fireAttributeEvent(AttributeItemRemoved::class, $attribute, $item, false);

        return $item;
    }

    public function hasGetMutator($key): bool
    {
        return \array_key_exists($key, $this->getCollectionAttributes()) || parent::hasGetMutator($key);
    }

    public function hasSetMutator($key): bool
    {
        return \array_key_exists($key, $this->getCollectionAttributes()) || parent::hasSetMutator($key);
    }

    /**
     * @phpstan-return never
     */
    protected function throwItemNotFound(string $attribute, int|string $id): void
    {
        $modelId = $this->getKey();
        $model = __CLASS__;
        throw new ModelNotFoundException("No items with id \"$id\" in [$attribute] collection of model [$model] with id \"$modelId\"");
    }

    protected function mutateAttribute($key, $value)
    {
        if (parent::hasGetMutator($key)) {
            return parent::mutateAttribute($key, $value);
        }

        return $this->getAttributeCollection($key, $value);
    }

    protected function setMutatedAttributeValue($key, $value): void
    {
        if (parent::hasSetMutator($key)) {
            parent::setMutatedAttributeValue($key, $value);

            return;
        }

        if ($value !== null) {
            $value = $this->getAttributeCollection($key, $value);
            $value = $this->castAttributeAsJson($key, $value);
        }
        $this->attributes[$key] = $value;
    }

    protected function fireAttributeEvent(
        string $eventClass,
        string $attribute,
        AttributeCollectionItem $item,
        bool $halt = true
    ) {
        $method = $halt ? 'until' : 'dispatch';

        return $this->filterModelEventResults(
            static::$dispatcher->$method(new $eventClass($this, $attribute, $item))
        );
    }
}
