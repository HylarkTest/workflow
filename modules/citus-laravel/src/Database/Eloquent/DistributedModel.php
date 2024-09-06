<?php

declare(strict_types=1);

namespace CitusLaravel\Database\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LaravelUtils\Database\Eloquent\Relations\MorphToOne;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Concerns\HasRelationships;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use LaravelUtils\Database\Eloquent\Concerns\BetterRelationships;

/**
 * @template TDistributed of \Illuminate\Database\Eloquent\Model
 *
 * @property class-string<TDistributed> $distributedModel
 */
trait DistributedModel
{
    use BetterRelationships {
        BetterRelationships::belongsToMany as unscopedBelongsToMany;
        BetterRelationships::morphToMany as unscopedMorphToMany;
        BetterRelationships::morphedByMany as unscopedMorphedByMany;
        BetterRelationships::belongsToOne as unscopedBelongsToOne;
        BetterRelationships::morphToOne as unscopedMorphToOne;
    }
    use HasRelationships {
        HasRelationships::belongsToMany as ignoredBelongsToMany;
        HasRelationships::morphToMany as ignoredMorphToMany;
        HasRelationships::morphedByMany as ignoredMorphedByMany;
        HasRelationships::newBelongsTo as unscopedNewBelongsTo;
    }

    /**
     * @return class-string<TDistributed>
     */
    public function getDistributedModelClass(): string
    {
        return $this->distributedModel;
    }

    public function getDistributedColumn(): string
    {
        $distributedModelClass = $this->getDistributedModelClass();

        return (new $distributedModelClass)->getForeignKey();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<TDistributed, self>
     *
     * @phpstan-ignore-next-line
     */
    public function distributedModel(): BelongsTo
    {
        return $this->belongsTo($this->getDistributedModelClass(), $this->getDistributedColumn());
    }

    public function getQualifiedDistributedColumn(): string
    {
        return $this->getTable().'.'.$this->getDistributedColumn();
    }

    /**
     * @template T of \Illuminate\Database\Eloquent\Model
     *
     * @param  class-string<T>  $related
     * @param  string|null  $table
     * @param  string|null  $foreignPivotKey
     * @param  string|null  $relatedPivotKey
     * @param  string|null  $parentKey
     * @param  string|null  $relatedKey
     * @param  string|null  $relation
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany<T>
     */
    public function belongsToMany($related, $table = null, $foreignPivotKey = null,
        $relatedPivotKey = null, $parentKey = null,
        $relatedKey = null, $relation = null)
    {
        /** @var \Illuminate\Database\Eloquent\Relations\BelongsToMany<T> $unscopedRelation */
        $unscopedRelation = $this->unscopedBelongsToMany(
            $related,
            $table,
            $foreignPivotKey,
            $relatedPivotKey,
            $parentKey,
            $relatedKey,
            $relation,
        );

        return $this->addDistributedPivotValue($unscopedRelation);
    }

    /**
     * @template T of \Illuminate\Database\Eloquent\Model
     *
     * @param  class-string<T>  $related
     * @param  string  $name
     * @param  string|null  $table
     * @param  string|null  $foreignPivotKey
     * @param  string|null  $relatedPivotKey
     * @param  string|null  $parentKey
     * @param  string|null  $relatedKey
     * @param  string|null  $relation
     * @param  bool  $inverse
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany<T>
     */
    public function morphToMany($related, $name, $table = null, $foreignPivotKey = null,
        $relatedPivotKey = null, $parentKey = null,
        $relatedKey = null, $relation = null, $inverse = false)
    {
        /** @var \Illuminate\Database\Eloquent\Relations\MorphToMany<T> $unscopedRelation */
        $unscopedRelation = $this->unscopedMorphToMany(
            $related,
            $name,
            $table,
            $foreignPivotKey,
            $relatedPivotKey,
            $parentKey,
            $relatedKey,
            $relation,
            $inverse,
        );

        return $this->addDistributedPivotValue($unscopedRelation);
    }

    /**
     * @template T of \Illuminate\Database\Eloquent\Model
     *
     * @param  class-string<T>  $related
     * @param  string  $name
     * @param  string|null  $table
     * @param  string|null  $foreignPivotKey
     * @param  string|null  $relatedPivotKey
     * @param  string|null  $parentKey
     * @param  string|null  $relatedKey
     * @param  string|null  $relation
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany<T>
     */
    public function morphedByMany($related, $name, $table = null, $foreignPivotKey = null,
        $relatedPivotKey = null, $parentKey = null, $relatedKey = null, $relation = null)
    {
        /** @var \Illuminate\Database\Eloquent\Relations\MorphToMany<T> $unscopedRelation */
        $unscopedRelation = $this->unscopedMorphedByMany(
            $related,
            $name,
            $table,
            $foreignPivotKey,
            $relatedPivotKey,
            $parentKey,
            $relatedKey,
            $relation,
        );

        return $this->addDistributedPivotValue($unscopedRelation);
    }

    /**
     * @template T of \Illuminate\Database\Eloquent\Model
     *
     * @param  \Illuminate\Database\Eloquent\Builder<T>  $query
     * @param  T  $child
     * @param  string  $foreignKey
     * @param  string  $ownerKey
     * @param  string  $relation
     * @return \CitusLaravel\Database\Eloquent\DistributedBelongsTo<T, self>
     *
     * @phpstan-ignore-next-line Not sure how to resolve this as the constructor is in Laravel
     */
    protected function newBelongsTo(EloquentBuilder $query, Model $child, $foreignKey, $ownerKey, $relation)
    {
        /** @phpstan-ignore-next-line Not sure how to resolve this as the constructor is in Laravel */
        return new DistributedBelongsTo($query, $child, $foreignKey, $ownerKey, $relation);
    }

    /**
     * @template T of \Illuminate\Database\Eloquent\Model
     *
     * @param  class-string<T>  $related
     * @return \LaravelUtils\Database\Eloquent\Relations\BelongsToOne<T>
     */
    protected function belongsToOne(string $related, ?string $table = null, ?string $foreignPivotKey = null,
        ?string $relatedPivotKey = null, ?string $parentKey = null,
        ?string $relatedKey = null, ?string $relation = null)
    {
        /** @var \LaravelUtils\Database\Eloquent\Relations\BelongsToOne<T> $unscopedRelation */
        $unscopedRelation = $this->unscopedBelongsToOne(
            $related,
            $table,
            $foreignPivotKey,
            $relatedPivotKey,
            $parentKey,
            $relatedKey,
            $relation
        );

        return $this->addDistributedPivotValue($unscopedRelation);
    }

    /**
     * @template T of \Illuminate\Database\Eloquent\Model
     *
     * @param  class-string<T>  $related
     * @return \LaravelUtils\Database\Eloquent\Relations\MorphToOne<T>
     */
    protected function morphToOne(string $related, string $name, ?string $table = null,
        ?string $foreignPivotKey = null, ?string $relatedPivotKey = null,
        ?string $parentKey = null, ?string $relatedKey = null, bool $inverse = false): MorphToOne
    {
        /** @var \LaravelUtils\Database\Eloquent\Relations\MorphToOne<T> $unscopedRelation */
        $unscopedRelation = $this->baseMorphToOne(
            $related,
            $name,
            $table,
            $foreignPivotKey,
            $relatedPivotKey,
            $parentKey,
            $relatedKey,
            $inverse
        );

        return $this->addDistributedPivotValue($unscopedRelation);
    }

    /**
     * @template TModel of \Illuminate\Database\Eloquent\Model
     * @template TRelation of \Illuminate\Database\Eloquent\Relations\BelongsToMany<TModel>
     *
     * @param  TRelation  $relation
     * @return TRelation
     */
    protected function addDistributedPivotValue(BelongsToMany $relation): BelongsToMany
    {
        $columnValue = $this->getDistributedColumnValue();

        if (! $columnValue) {
            throw new \Exception('Cannot filter pivot values without a distributed column value');
        }

        $pivotClass = $relation->getPivotClass();
        /** @var \Illuminate\Database\Eloquent\Relations\Pivot $pivot */
        $pivot = new $pivotClass;
        if (\in_array(DistributedModel::class, class_uses_recursive($pivotClass), true)) {
            /** @phpstan-ignore-next-line We know this exists from the check */
            $distributedColumn = $pivot->getDistributedColumn();
        } else {
            $distributedColumn = $this->getDistributedColumn();
        }

        return $relation->withPivotValue($distributedColumn, $columnValue);
    }

    /**
     * @return TDistributed
     *
     * @throws \Exception
     */
    protected function getCurrentDistributedModel(): ?Model
    {
        throw new \Exception('Not implemented');
    }

    protected function getDistributedColumnValue(): ?int
    {
        return $this->getCurrentDistributedModel()?->getKey();
    }
}
