<?php

declare(strict_types=1);

namespace Mappings\Core\Mappings\Relationships;

use GraphQL\Deferred;
use Mappings\Models\Item;
use LighthouseHelpers\Utils;
use Mappings\Models\Mapping;
use Illuminate\Support\Carbon;
use Mappings\Events\RelationshipSet;
use Illuminate\Support\Facades\Event;
use PHPStan\ShouldNotHappenException;
use Mappings\Events\RelationshipUnset;
use Mappings\Events\RelationshipsAdded;
use Mappings\Events\RelationshipsRemoved;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Collection;
use LighthouseHelpers\Core\ModelBatchLoader;
use LaravelUtils\Database\Eloquent\Contracts\AttributeCollectionItem;

/**
 * @implements \Illuminate\Contracts\Support\Arrayable<int, mixed>
 *
 * @phpstan-type NewRelationshipOptions = array{
 *     name?: string,
 *     apiName?: string,
 *     to: string|\Mappings\Models\Mapping,
 *     type: \Mappings\Core\Mappings\Relationships\RelationshipType|string,
 *     inverse?: bool,
 * }
 * @phpstan-type RelationshipOptions = array{
 *     id?: string,
 *     name: string,
 *     apiName?: string,
 *     to: string|\Mappings\Models\Mapping,
 *     createdAt?: string,
 *     updatedAt?: string,
 *     type: \Mappings\Core\Mappings\Relationships\RelationshipType|string,
 *     inverse?: bool,
 *     apiType?: string,
 * }
 */
class Relationship implements Arrayable, AttributeCollectionItem
{
    public const MAX_LENGTH = 50;

    public RelationshipType $type;

    public string $id;

    public string $name;

    public string $apiName;

    public string $apiType;

    public string|Mapping $to;

    public bool $inverse;

    public string $createdAt;

    public string $updatedAt;

    /**
     * @param  RelationshipOptions  $relationship
     */
    public function __construct(array $relationship)
    {
        $this->type = $relationship['type'] instanceof RelationshipType ?
            $relationship['type'] :
            RelationshipType::from($relationship['type']);

        $this->id = $relationship['id'] ?? Utils::generateRandomString();
        $this->name = $relationship['name'];
        $this->to = $relationship['to'] instanceof Mapping ? $relationship['to'] : (string) $relationship['to'];
        $this->inverse = $relationship['inverse'] ?? false;
        $this->apiName = $relationship['apiName'] ?? Utils::generateGraphQLType($relationship['name']);
        $this->apiType = ucfirst($this->apiName);
        $this->createdAt = $relationship['createdAt'] ?? (string) Carbon::now();
        $this->updatedAt = $relationship['updatedAt'] ?? (string) Carbon::now();
    }

    public function isToMany(): bool
    {
        return $this->type->isToMany();
    }

    public function isToOne(): bool
    {
        return $this->type->isToOne();
    }

    public function toMapping(): Mapping
    {
        $mappingModel = $this->to instanceof Mapping ?
            $this->to :
            config('mappings.models.mapping')::query()->find($this->to);

        $this->to = $mappingModel ?? '';

        if ($this->to instanceof Mapping) {
            return $this->to;
        }
        throw new ShouldNotHappenException;
    }

    public function inverseRelationship(): ?self
    {
        return $this->toMapping()->relationships->firstWhere('id', $this->id);
    }

    public function toId(): string
    {
        return $this->to instanceof Mapping ? (string) $this->to->getKey() : $this->to;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'apiName' => $this->apiName,
            'to' => $this->toId(),
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
            'type' => $this->type->value,
            'inverse' => $this->inverse,
        ];
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Collection<int, \Mappings\Models\Item>|\App\Models\Item  $related
     */
    public function add(Item $parent, Collection|Item $related): void
    {
        if ($related instanceof Item) {
            $related = (new Item)->newCollection([$related]);
        }
        $parent->relatedItems($this)->attach($related);

        Event::dispatch(new RelationshipsAdded($this, $related, $parent));
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Collection<int, \Mappings\Models\Item>|null  $related
     */
    public function remove(Item $parent, ?Collection $related = null): void
    {
        if ($this->isToMany()) {
            /** @var \Illuminate\Database\Eloquent\Collection<int, \Mappings\Models\Item> $related */
            $parent->relatedItems($this)->detach($related);

            Event::dispatch(new RelationshipsRemoved($this, $related, $parent));
        } else {
            $original = $parent->relatedItems($this)->first();
            if ($original) {
                $parent->relatedItems($this)->sync([]);

                Event::dispatch(new RelationshipUnset($this, $original, $parent));
            }
        }
    }

    public function set(Item $parent, Item $related): void
    {
        $parent->relatedItems($this)->sync($related);

        Event::dispatch(new RelationshipSet($this, $related, $parent));
    }

    public function id(): string
    {
        return $this->id;
    }

    public function coreId(): string
    {
        $id = $this->id();

        return str_ends_with($id, '_inverse') ? mb_substr($id, 0, -8) : $id;
    }

    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }

    public function getInverseId(): string
    {
        $id = $this->id();

        return str_ends_with($id, '_inverse') ? mb_substr($id, 0, -8) : $id.'_inverse';
    }

    protected function batchedInverseRelationship(): Deferred
    {
        return ModelBatchLoader::instanceFromModel(
            config('mappings.models.mapping')
        )->loadAndResolve(
            $this->toId(),
            [],
            fn (Mapping $mapping) => $mapping->relationships->firstWhere('id', $this->id)
        );
    }
}
