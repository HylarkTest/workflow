<?php

declare(strict_types=1);

namespace Mappings\Models\Relationships;

use Mappings\Models\Item;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Database\Eloquent\Collection;
use Mappings\Core\Mappings\Relationships\Relationship;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @extends \Illuminate\Database\Eloquent\Relations\BelongsToMany<\Mappings\Models\Item>
 */
class CustomRelationship extends BelongsToMany
{
    protected Relationship $relationship;

    public function __construct(Item $parent, Relationship $relationship)
    {
        $this->relationship = $relationship;
        $foreignPivotKey = $relationship->inverse ? 'related_id' : 'foreign_id';
        $relatedPivotKey = $relationship->inverse ? 'foreign_id' : 'related_id';
        parent::__construct(
            $parent->newInstance()->newQuery(),
            $parent,
            'relationships',
            $foreignPivotKey,
            $relatedPivotKey,
            $parent->getKeyName(),
            $parent->getKeyName(),
            $relationship->apiName
        );
    }

    /**
     * Get the results of the relationship.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, \Mappings\Models\Item>|\Mappings\Models\Item|null
     */
    public function getResults()
    {
        if ($this->relationship->type->isToOne()) {
            return $this->first();
        }

        return $this->parent->{$this->parentKey} !== null
            ? $this->get()
            : $this->related->newCollection();
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function attach($id, array $attributes = [], $touch = true): void
    {
        parent::attach(
            $id,
            array_merge($attributes, ['relation_id' => $this->relationship->coreId()]),
            $touch
        );
    }

    /**
     * Match the eagerly loaded results to their many parents.
     *
     * @param  \Illuminate\Database\Eloquent\Model[]  $models
     * @param  string  $relation
     * @param  \Illuminate\Database\Eloquent\Collection<int, \Mappings\Models\Item>  $results
     * @return \Illuminate\Database\Eloquent\Model[]
     */
    public function match(array $models, Collection $results, $relation)
    {
        $dictionary = $this->buildDictionary($results);

        // Once we have the dictionary we can simply spin through the parent models to
        // link them up with their children using the keyed dictionary to make the
        // matching very convenient and easy work. Then we'll just return them.
        foreach ($models as $model) {
            if (isset($dictionary[$key = $model->{$this->parentKey}])) {
                $model->setRelation(
                    $relation, $this->getRelationValue($dictionary, $key)
                );
            }
        }

        return $models;
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Model[]  $models
     * @return \Illuminate\Database\Eloquent\Model[]
     */
    public function initRelation(array $models, $relation)
    {
        foreach ($models as $model) {
            $model->setRelation($relation, $this->relationship->isToOne() ? null : $this->related->newCollection());
        }

        return $models;
    }

    public function newPivotQuery(): Builder
    {
        $query = parent::newPivotQuery();

        return $query->where('relation_id', $this->relationship->coreId());
    }

    /**
     * Set the join clause for the relation query.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<\Mappings\Models\Item>|null  $query
     * @return $this
     */
    protected function performJoin($query = null)
    {
        $query = $query ?: $this->query;

        // We need to join to the intermediate table on the related model's primary
        // key column with the intermediate table's foreign key for the related
        // model instance. Then we can set the "where" for the parent models.
        $baseTable = $this->related->getTable();

        $key = $baseTable.'.'.$this->relatedKey;

        $query->join($this->table, function (JoinClause $query) use ($key) {
            $query->on(function (Builder $query) use ($key) {
                $query->whereColumn($key, $this->getQualifiedRelatedPivotKeyName())
                    ->where('relation_id', $this->relationship->coreId())
                    ->whereColumn($this->related->qualifyColumn('base_id'), $this->qualifyPivotColumn('base_id'));
            });
        });

        return $this;
    }

    /**
     * Get the value of a relationship by one or many type.
     *
     * @param  array<string|int, \Mappings\Models\Item[]>  $dictionary
     * @param  string|int  $key
     * @return \Mappings\Models\Item|\Illuminate\Database\Eloquent\Collection<int, \Mappings\Models\Item>
     */
    protected function getRelationValue(array $dictionary, $key): Item|Collection
    {
        $value = $dictionary[$key];

        /** @var \Mappings\Models\Item|\Illuminate\Database\Eloquent\Collection<int, \Mappings\Models\Item> $relationValue */
        $relationValue = $this->relationship->isToOne() ? reset($value) : $this->related->newCollection($value);

        return $relationValue;
    }
}
