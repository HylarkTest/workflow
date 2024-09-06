<?php

declare(strict_types=1);

namespace LaravelUtils\Database\Eloquent\Concerns;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use LaravelUtils\Database\Eloquent\Relations\MorphToOne;
use LaravelUtils\Database\Eloquent\Relations\BelongsToOne;

/**
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait HasAdvancedRelationships
{
    /**
     * The many-to-many relationship methods.
     *
     * @var string[]
     */
    public static array $oneMethods = [
        'belongsToOne', 'morphToOne',
    ];

    /**
     * Define a many-to-many relationship.
     *
     * @template T of \Illuminate\Database\Eloquent\Model
     *
     * @param  class-string<T>  $related
     * @return \LaravelUtils\Database\Eloquent\Relations\BelongsToOne<T>
     */
    public function belongsToOne(
        string $related,
        ?string $table = null,
        ?string $foreignPivotKey = null,
        ?string $relatedPivotKey = null,
        ?string $parentKey = null,
        ?string $relatedKey = null,
        ?string $relation = null
    ): BelongsToOne {
        // If no relationship name was passed, we will pull backtraces to get the
        // name of the calling function. We will use that function name as the
        // title of this relation since that is a great convention to apply.
        if ($relation === null) {
            $relation = $this->guessBelongsToOneRelation();
        }

        // First, we'll need to determine the foreign key and "other key" for the
        // relationship. Once we have determined the keys we'll make the query
        // instances as well as the relationship instances we need for this.
        $instance = $this->newRelatedInstance($related);

        $foreignPivotKey = $foreignPivotKey ?: $this->getForeignKey();

        $relatedPivotKey = $relatedPivotKey ?: $instance->getForeignKey();

        // If no table name was provided, we can guess it by concatenating the two
        // models using underscores in alphabetical order. The two model names
        // are transformed to snake case from their default CamelCase also.
        if ($table === null) {
            $table = $this->joiningTable($related, $instance);
        }

        return $this->newBelongsToOne(
            $instance->newQuery(), $this, $table, $foreignPivotKey,
            $relatedPivotKey, $parentKey ?: $this->getKeyName(),
            $relatedKey ?: $instance->getKeyName(), $relation
        );
    }

    /**
     * Define a polymorphic many-to-many relationship.
     *
     * @template T of \Illuminate\Database\Eloquent\Model
     *
     * @param  class-string<T>  $related
     * @return \LaravelUtils\Database\Eloquent\Relations\MorphToOne<T>
     */
    public function morphToOne(
        string $related,
        string $name,
        ?string $table = null,
        ?string $foreignPivotKey = null,
        ?string $relatedPivotKey = null,
        ?string $parentKey = null,
        ?string $relatedKey = null,
        bool $inverse = false
    ): MorphToOne {
        $caller = $this->guessBelongsToOneRelation();

        // First, we will need to determine the foreign key and "other key" for the
        // relationship. Once we have determined the keys we will make the query
        // instances, as well as the relationship instances we need for these.
        $instance = $this->newRelatedInstance($related);

        $foreignPivotKey = $foreignPivotKey ?: $name.'_id';

        $relatedPivotKey = $relatedPivotKey ?: $instance->getForeignKey();

        // Now we're ready to create a new query builder for this related model and
        // the relationship instances for this relation. This relations will set
        // appropriate query constraints then entirely manages the hydrations.
        if (! $table) {
            $words = preg_split('/(_)/u', $name, -1, \PREG_SPLIT_DELIM_CAPTURE);

            $lastWord = array_pop($words);

            $table = implode('', $words).Str::plural($lastWord);
        }

        return $this->newMorphToOne(
            $instance->newQuery(), $this, $name, $table,
            $foreignPivotKey, $relatedPivotKey, $parentKey ?: $this->getKeyName(),
            $relatedKey ?: $instance->getKeyName(), $caller, $inverse
        );
    }

    /**
     * Define a polymorphic, inverse many-to-many relationship.
     *
     * @template T of \Illuminate\Database\Eloquent\Model
     *
     * @param  class-string<T>  $related
     * @return \LaravelUtils\Database\Eloquent\Relations\MorphToOne<T>
     */
    public function morphedByOne(
        string $related,
        string $name,
        ?string $table = null,
        ?string $foreignPivotKey = null,
        ?string $relatedPivotKey = null,
        ?string $parentKey = null,
        ?string $relatedKey = null
    ): MorphToOne {
        $foreignPivotKey = $foreignPivotKey ?: $this->getForeignKey();

        // For the inverse of the polymorphic many-to-many relations, we will change
        // the way we determine the foreign and other keys, as it is the opposite
        // of the morph-to-many method since we're figuring out these inverses.
        $relatedPivotKey = $relatedPivotKey ?: $name.'_id';

        return $this->morphToOne(
            $related, $name, $table, $foreignPivotKey,
            $relatedPivotKey, $parentKey, $relatedKey, true
        );
    }

    /**
     * Instantiate a new BelongsToMany relationship.
     *
     * @template T of \Illuminate\Database\Eloquent\Model
     *
     * @param  \Illuminate\Database\Eloquent\Builder<T>  $query
     * @return \LaravelUtils\Database\Eloquent\Relations\BelongsToOne<T>
     */
    protected function newBelongsToOne(
        Builder $query,
        Model $parent,
        string $table,
        string $foreignPivotKey,
        string $relatedPivotKey,
        string $parentKey,
        string $relatedKey,
        ?string $relationName = null
    ): BelongsToOne {
        return new BelongsToOne($query, $parent, $table, $foreignPivotKey, $relatedPivotKey, $parentKey, $relatedKey, $relationName);
    }

    /**
     * Instantiate a new MorphToOne relationship.
     *
     * @template T of \Illuminate\Database\Eloquent\Model
     *
     * @param  \Illuminate\Database\Eloquent\Builder<T>  $query
     * @return \LaravelUtils\Database\Eloquent\Relations\MorphToOne<T>
     */
    protected function newMorphToOne(
        Builder $query,
        Model $parent,
        string $name,
        string $table,
        string $foreignPivotKey,
        string $relatedPivotKey,
        string $parentKey,
        string $relatedKey,
        ?string $relationName = null,
        bool $inverse = false
    ): MorphToOne {
        return new MorphToOne($query, $parent, $name, $table, $foreignPivotKey, $relatedPivotKey, $parentKey, $relatedKey,
            $relationName, $inverse);
    }

    /**
     * Get the relationship name of the belongsToMany relationship.
     */
    protected function guessBelongsToOneRelation(): ?string
    {
        $caller = Arr::first(debug_backtrace(\DEBUG_BACKTRACE_IGNORE_ARGS), function ($trace) {
            return ! \in_array(
                $trace['function'],
                array_merge(static::$oneMethods, ['guessBelongsToManyRelation']), true
            );
        });

        return $caller !== null ? $caller['function'] : null;
    }
}
