<?php

declare(strict_types=1);

namespace App\Models\Relations;

use App\Models\Mapping;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

/**
 * @extends \Illuminate\Database\Eloquent\Relations\MorphToMany<\App\Models\Mapping>
 */
class MappingsThroughDomain extends MorphToMany
{
    protected string $spaceKey;

    /**
     * Create a new morph to many relationship instance.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $parent
     * @param  string  $name
     * @param  string  $table
     * @param  string  $foreignPivotKey
     * @param  string  $relatedPivotKey
     * @param  string  $parentKey
     * @param  string  $relationName
     */
    public function __construct($parent, $name, $table, $foreignPivotKey,
        $relatedPivotKey, $parentKey, $relationName)
    {
        $instance = new Mapping;
        $this->morphClass = $instance->getMorphClass();
        $this->spaceKey = $instance->space()->getForeignKeyName();

        parent::__construct(
            $instance->newQuery(), $parent, $name, $table, $foreignPivotKey,
            $relatedPivotKey, $parentKey, $instance->getKeyName(), $relationName, true
        );

        $this->query->distinct();
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<\App\Models\Mapping>|null  $query
     */
    public function performJoin($query = null): static
    {
        $query = $query ?: $this->query;

        $baseTable = $this->related->getTable();

        $key = $baseTable.'.'.$this->relatedKey;
        $spaceKey = $baseTable.'.'.$this->spaceKey;

        $query->join($this->table, function (JoinClause $query) use ($key, $spaceKey) {
            $query->on(function (Builder $query) use ($key) {
                $query->whereColumn($key, $this->getQualifiedRelatedPivotKeyName())
                    ->where('domain_type', $this->morphClass);
            })->orWhere(function (Builder $query) use ($spaceKey) {
                $query->whereColumn($spaceKey, $this->getQualifiedRelatedPivotKeyName())
                    ->where('domain_type', 'spaces');
            });
        });

        return $this;
    }

    public function addWhereConstraints(): static
    {
        $this->query->where(
            $this->getQualifiedForeignPivotKeyName(), '=', $this->parent->{$this->parentKey}
        );

        return $this;
    }

    /**
     * Set the constraints for an eager load of the relation.
     *
     * @param  \Illuminate\Database\Eloquent\Model[]  $models
     * @return void
     */
    public function addEagerConstraints(array $models)
    {
        $whereIn = $this->whereInMethod($this->parent, $this->parentKey);

        $this->query->{$whereIn}(
            $this->getQualifiedForeignPivotKeyName(),
            $this->getKeys($models, $this->parentKey)
        );
    }

    /**
     * Get the pivot columns for the relation.
     * We need to override the parent implementation because we can't use
     * distinct with the foreign keys.
     *
     * "pivot_" is prefixed ot each column for easy removal later.
     *
     * @return string[]
     */
    protected function aliasedPivotColumns()
    {
        $defaults = [$this->foreignPivotKey];

        return collect(array_merge($defaults, $this->pivotColumns))
            ->map(fn ($column) => $this->table.'.'.$column.' as pivot_'.$column)
            ->unique()
            ->all();
    }
}
