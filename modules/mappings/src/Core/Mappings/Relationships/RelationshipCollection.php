<?php

declare(strict_types=1);

namespace Mappings\Core\Mappings\Relationships;

use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Model;
use Mappings\Core\Mappings\Concerns\HasUniqueNames;
use LaravelUtils\Database\Eloquent\AttributeCollection;

/**
 * @extends \LaravelUtils\Database\Eloquent\AttributeCollection<int, \Mappings\Core\Mappings\Relationships\Relationship>
 *
 * @phpstan-import-type RelationshipOptions from \Mappings\Core\Mappings\Relationships\Relationship
 * @phpstan-import-type NewRelationshipOptions from \Mappings\Core\Mappings\Relationships\Relationship
 */
class RelationshipCollection extends AttributeCollection
{
    use HasUniqueNames;

    public static function makeFromAttribute($items, Model $model): self
    {
        if ($items instanceof static) {
            return $items;
        }
        $relationships = [];

        foreach ((array) $items as $item) {
            $relationships[] = $item instanceof Relationship ? $item : new Relationship($item);
        }

        return new self($relationships);
    }

    /**
     * @param  NewRelationshipOptions  $args
     * @return \Mappings\Core\Mappings\Relationships\Relationship
     */
    public function addItem(array $args, Model $model)
    {
        /** @var \Mappings\Core\Mappings\Relationships\RelationshipType $type */
        $type = $args['type'];
        /** @var \Mappings\Models\Mapping $to */
        $to = $args['to'];
        $args['name'] = $args['name'] ?? ($type->isToOne() ? $to->singular_name : $to->name);
        $args['inverse'] = $args['inverse'] ?? false;

        $relationship = new Relationship($args);

        $relationship->apiName = $this->getUniqueName($relationship->apiName);

        $this->push($relationship);

        return $relationship;
    }

    /**
     * @param  string  $id
     * @param array{
     *     name?: string,
     *     apiName?: string,
     * }  $args
     * @return \Mappings\Core\Mappings\Relationships\Relationship|null
     */
    public function changeItem($id, array $args, Model $model)
    {
        $originalKey = $this->findIndex($id);

        if ($originalKey === false) {
            return null;
        }

        /** @var \Mappings\Core\Mappings\Relationships\Relationship $relationship */
        $relationship = $this[$originalKey];

        $name = $args['name'] ?? $relationship->name;
        $apiName = $args['apiName'] ?? $relationship->apiName;

        if ($name !== $relationship->name || $apiName !== $relationship->apiName) {
            $relationship->updatedAt = (string) Carbon::now();
            $relationship->name = $name;
            $relationship->apiName = $apiName;
        }

        return $relationship;
    }

    /**
     * @param  string  $id
     * @return \Mappings\Core\Mappings\Relationships\Relationship|null
     */
    public function removeItem($id, Model $model)
    {
        return $this->forgetItem($id);
    }
}
