<?php

declare(strict_types=1);

namespace LaravelUtils\Database\Eloquent\Relations\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;

trait InteractsWithPivotTable
{
    /**
     * @phpstan-ignore-next-line This is meant to be different from the parent class.
     */
    public function getResults(): ?Model
    {
        return $this->parent->{$this->parentKey} !== null
            ? $this->first()
            : null;
    }

    /**
     * Initialize the relation on a set of models.
     *
     * @param  array<array-key, \Illuminate\Database\Eloquent\Model>  $models
     * @param  string  $relation
     */
    public function initRelation(array $models, $relation): array
    {
        foreach ($models as $model) {
            $model->setRelation($relation, null);
        }

        return $models;
    }

    /**
     * Match the eagerly loaded results to their many parents.
     *
     * @param  array<array-key, \Illuminate\Database\Eloquent\Model>  $models
     * @param  \Illuminate\Database\Eloquent\Collection<array-key, \Illuminate\Database\Eloquent\Model>  $results
     * @param  string  $relation
     * @return array<array-key, \Illuminate\Database\Eloquent\Model>
     */
    public function match(array $models, Collection $results, $relation): array
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
     * Attach a model to the parent.
     *
     * @param  int|\Illuminate\Database\Eloquent\Model  $id
     * @param  mixed  $touch
     * @return void
     */
    public function attach($id, array $attributes = [], $touch = true)
    {
        if ($this->using) {
            $this->attachUsingCustomClass($id, $attributes);
        } else {
            $hasTimestamps = ($this->hasPivotColumn($this->createdAt())
                || $this->hasPivotColumn($this->updatedAt()));
            $id = $this->parseId($id);
            $this->newPivotStatement()->updateOrInsert(
                $this->buildConstraints(),
                $this->formatAttachRecord($id, [], $attributes, $hasTimestamps),
            );
        }

        if ($touch) {
            $this->touchIfTouching();
        }
    }

    /**
     * @param  bool  $touch
     * @param  mixed|null  $ids
     */
    public function detach($ids = null, $touch = true): int
    {
        if (\func_num_args() === 2 || (\func_num_args() === 1 && ! \is_bool($ids))) {
            throw new \BadMethodCallException('The detach method should not include the ids of the models being detached');
        }

        return parent::detach(null, $touch);
    }

    /**
     * @param  bool  $detaching
     * @param  mixed  $ids
     * @return void
     */
    public function sync($ids, $detaching = true)
    {
        throw new \BadMethodCallException;
    }

    /**
     * @return array<string, string>
     */
    protected function buildConstraints(): array
    {
        return [
            $this->relatedPivotKey => $this->parent->{$this->parentKey},
        ];
    }

    protected function attachUsingCustomClass($id, array $attributes): void
    {
        $hasTimestamps = ($this->hasPivotColumn($this->createdAt())
            || $this->hasPivotColumn($this->updatedAt()));

        $record = $this->formatAttachRecord(
            $this->parseId($id), [], $attributes, $hasTimestamps
        );

        $this->newPivot($record, false)->save();
    }

    protected function getRelationValue(array $dictionary, int $key): mixed
    {
        $value = $dictionary[$key];

        return reset($value);
    }
}
