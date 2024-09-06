<?php

declare(strict_types=1);

namespace Markers\Core;

use Markers\Models\Marker;
use Markers\Models\MarkablePivot;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

/**
 * @template TRelatedModel of \Markers\Models\Marker
 *
 * @extends \Illuminate\Database\Eloquent\Relations\MorphToMany<TRelatedModel>
 *
 * TODO: Create a generic relation in LaravelUtils that allows multiple or single results
 */
class PivotMarkersRelation extends MorphToMany
{
    protected bool $single;

    /**
     * @var \Markers\Models\Marker
     */
    protected $related;

    /**
     * @param  TRelatedModel  $instance
     * @param  string  $relationName
     */
    public function __construct(Model $parent, Marker $instance, $relationName = 'markers', bool $single = false)
    {
        $parent = $parent->relationLoaded('pivot') ? $parent->getRelation('pivot') : new MarkablePivot;

        if (! ($parent instanceof MarkablePivot)) {
            throw new \OutOfBoundsException('The pivot property must be an instance of [MarkergablePivot]');
        }

        parent::__construct(
            $instance->newQuery(),
            $parent,
            'markable',
            'markables',
            'markable_id',
            $instance->getForeignKey(),
            $parent->getKeyName(),
            $instance->getKeyName(),
            $relationName
        );
        $this->single = $single;
    }

    /**
     * @param  array<int, \Illuminate\Database\Eloquent\Model>  $models
     * @return array<int, \Illuminate\Database\Eloquent\Model>
     */
    public function initRelation(array $models, $relation)
    {
        foreach ($models as $model) {
            $default = $this->single ? null : $this->related->newCollection();
            /** @var \Illuminate\Database\Eloquent\Relations\Pivot $pivot */
            $pivot = $model->getRelation('pivot');
            $pivot->setRelation($this->relationName, $default);
            $model->setRelation('pivot'.ucfirst($this->relationName), $default);
        }

        return $models;
    }

    /**
     * @param  array<int, \Illuminate\Database\Eloquent\Model>  $models
     * @param  \Illuminate\Database\Eloquent\Collection<array-key, TRelatedModel>  $results
     * @param  mixed  $relation
     * @return array<int, \Illuminate\Database\Eloquent\Model>
     */
    public function match(array $models, Collection $results, $relation)
    {
        $dictionary = $this->buildDictionary($results);

        foreach ($models as $model) {
            /** @var \Illuminate\Database\Eloquent\Relations\Pivot $pivot */
            $pivot = $model->getRelation('pivot');
            if (isset($dictionary[$key = $pivot->getAttribute($this->parentKey)])) {
                $value = $dictionary[$key];
                $result = $this->single ?
                    reset($value) :
                    $this->related->newCollection($value);
                $pivot->setRelation($this->relationName, $result);
                $model->setRelation('pivot'.ucfirst($this->relationName), $result);
            }
        }

        return $models;
    }

    /**
     * @return TRelatedModel|\Markers\Models\Collections\MarkerCollection|null
     */
    public function getResults()
    {
        if ($this->parent->{$this->parentKey} === null) {
            return $this->single ? null : $this->related->newCollection();
        }

        return $this->single ?
            $this->first() :
            $this->get();
    }

    /**
     * @param  array<int, \Illuminate\Database\Eloquent\Model>  $models
     * @param  string  $key
     * @return array<int, int>
     */
    protected function getKeys(array $models, $key = null)
    {
        return collect($models)->map(function (Model $value) use ($key) {
            /** @var \Illuminate\Database\Eloquent\Relations\Pivot $pivot */
            $pivot = $value->getRelation('pivot');

            return $pivot->getAttribute($key ?? $pivot->getKeyName());
        })->values()->unique(null, true)->sort()->all();
    }
}
