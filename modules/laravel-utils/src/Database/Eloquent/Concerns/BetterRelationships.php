<?php

declare(strict_types=1);

namespace LaravelUtils\Database\Eloquent\Concerns;

use LaravelUtils\Database\Eloquent\Relations\MorphToOne;
use LaravelUtils\Database\Eloquent\Relations\BelongsToOne;

/**
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait BetterRelationships
{
    use HasAdvancedRelationships {
        belongsToOne as baseBelongsToOne;
        morphToOne as baseMorphToOne;
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
        return parent::belongsToMany(
            $related,
            $table,
            $foreignPivotKey,
            $relatedPivotKey,
            $parentKey,
            $relatedKey,
            $relation,
        )->withTimestamps();
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
        return parent::morphToMany(
            $related,
            $name,
            $table,
            $foreignPivotKey,
            $relatedPivotKey,
            $parentKey,
            $relatedKey,
            $relation,
            $inverse,
        )->withTimestamps();
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
        return parent::morphedByMany(
            $related,
            $name,
            $table,
            $foreignPivotKey,
            $relatedPivotKey,
            $parentKey,
            $relatedKey,
            $relation,
        )->withTimestamps();
    }

    /**
     * @template T of \Illuminate\Database\Eloquent\Model
     *
     * @return \LaravelUtils\Database\Eloquent\Relations\BelongsToOne<T>
     */
    protected function belongsToOne(string $related, ?string $table = null, ?string $foreignPivotKey = null,
        ?string $relatedPivotKey = null, ?string $parentKey = null,
        ?string $relatedKey = null, ?string $relation = null): BelongsToOne
    {
        return $this->baseBelongsToOne(
            $related,
            $table,
            $foreignPivotKey,
            $relatedPivotKey,
            $parentKey,
            $relatedKey,
            $relation
        )->withTimestamps();
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
        return $this->baseMorphToOne(
            $related,
            $name,
            $table,
            $foreignPivotKey,
            $relatedPivotKey,
            $parentKey,
            $relatedKey,
            $inverse
        )->withTimestamps();
    }
}
