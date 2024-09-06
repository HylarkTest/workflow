<?php

declare(strict_types=1);

namespace App\Core\Mappings\Markers;

use App\Models\Item;
use GraphQL\Deferred;
use App\GraphQL\AppContext;
use App\Models\MarkerGroup;
use LighthouseHelpers\Utils;
use Markers\Core\MarkerType;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Model;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Contracts\Support\Arrayable;
use Mappings\Core\Mappings\Relationships\Relationship;
use Nuwave\Lighthouse\Execution\BatchLoader\BatchLoaderRegistry;
use Nuwave\Lighthouse\Execution\BatchLoader\RelationBatchLoader;
use Nuwave\Lighthouse\Execution\ModelsLoader\SimpleModelsLoader;
use LaravelUtils\Database\Eloquent\Contracts\AttributeCollectionItem;

/**
 * @implements \Illuminate\Contracts\Support\Arrayable<string, mixed>
 */
class MappingMarkerGroup implements Arrayable, AttributeCollectionItem
{
    public const MAX_LENGTH = 50;

    public MarkerType $type;

    public string $id;

    public string $name;

    public string $apiName;

    public string $apiType;

    public int $group;

    public ?Relationship $relationship;

    public string $createdAt;

    public string $updatedAt;

    protected Item $item;

    public function __construct(array $options)
    {
        $group = $options['group'];
        if ($group instanceof MarkerGroup) {
            $this->type = $group->type;
        } else {
            $this->type = $options['type'] instanceof MarkerType ?
                $options['type'] :
                MarkerType::from($options['type']);
        }

        $this->id = $options['id'] ?? Utils::generateRandomString();
        $this->name = $options['name'] ??
            ($group instanceof MarkerGroup ?
                $group->name :
                throw new \InvalidArgumentException('The name must be specified'));
        $this->apiName = $options['apiName'] ?? Utils::generateGraphQLType($this->name);
        $this->group = $group instanceof Model ? $group->getKey() : (int) $group;
        $this->relationship = $options['relationship'] ?? null;
        $this->apiType = ucfirst($this->apiName);
        $this->createdAt = $options['createdAt'] ?? (string) Carbon::now();
        $this->updatedAt = $options['updatedAt'] ?? (string) Carbon::now();
    }

    public function toArray(): array
    {
        $array = [
            'id' => $this->id,
            'name' => $this->name,
            'apiName' => $this->apiName,
            'type' => $this->type->value,
            'group' => $this->group,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
        ];
        if ($this->relationship) {
            $array['relationship'] = $this->relationship->id;
        }

        return $array;
    }

    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }

    /**
     * @return string
     */
    public function id()
    {
        return $this->id;
    }

    public function setItemForResolving(Item $item): void
    {
        $this->item = $item;
    }

    public function markerGroup(): MarkerGroup
    {
        return MarkerGroup::query()->findOrFail($this->group);
    }

    public function isSingle(): bool
    {
        return $this->type === MarkerType::STATUS;
    }

    public function resolveItemMarkers(self $root, array $args, AppContext $context, ResolveInfo $resolveInfo): Deferred
    {
        /** @var array<int|string> $path */
        $path = $resolveInfo->path;

        $relationName = $this->isSingle() ? 'marker' : 'markers';
        $decorateBuilder = fn (/* @var \Illuminate\Database\Eloquent\Builder $query */ $query) => $query->fromGroup($this->group);

        /** @var \Nuwave\Lighthouse\Execution\BatchLoader\RelationBatchLoader $instance */
        $instance = BatchLoaderRegistry::instance($path, fn () => new RelationBatchLoader(new SimpleModelsLoader($relationName, $decorateBuilder)));

        return $instance->load($this->item);
    }
}
