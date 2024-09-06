<?php

declare(strict_types=1);

namespace LaravelUtils\Database\Eloquent\Concerns;

use Illuminate\Database\Eloquent\Model;
use App\Models\Contracts\SoftDeleteModel;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use LaravelUtils\Database\Eloquent\Relations\MorphToOne;

/**
 * Trait CascadesMorphRelationships
 *
 * @mixin \Illuminate\Database\Eloquent\Model
 *
 * @property array $cascadeRelationships
 */
trait CascadesMorphRelationships
{
    protected static array $morphClasses = [
        MorphToMany::class,
        MorphMany::class,
        MorphOne::class,
        MorphToOne::class,
    ];

    /**
     * Cascade delete any morph relationships when a model is deleted
     */
    public static function bootCascadesMorphRelationships(): void
    {
        static::deleted(static function (self $model) {
            $model->cascadeDeleteMorphRelationships();
        });
    }

    protected function cascadeDeleteMorphRelationships(): void
    {
        if (($this instanceof SoftDeleteModel) && ! $this->isForceDeleting()) {
            return;
        }
        foreach ($this->cascadeRelationships as $key => $value) {
            $method = \is_int($key) ? $value : $key;
            $options = \is_int($key) ? [] : (array) $value;
            $relation = $this->{$method}();

            if (! \in_array($class = $relation::class, static::$morphClasses, true)) {
                $classes = implode(', ', static::$morphClasses);
                throw new \Exception("relationship $method has class $class but should be one of $classes");
            }

            /* @var \Illuminate\Database\Eloquent\Relations\MorphOne|\Illuminate\Database\Eloquent\Relations\MorphMany|\Illuminate\Database\Eloquent\Relations\MorphToMany $relation */

            $chunk = $options['chunk'] ?? 1000;

            $method = $relation instanceof MorphToMany ? 'detach' : 'delete';

            if (\in_array('quick', $options, true)) {
                if (isset($options['chunk'])) {
                    do {
                        $relation->limit($chunk)->{$method}();
                    } while ($relation->count());
                }
                $relation->{$method}();
            } else {
                $relation->each(function (Model $related) use ($relation) {
                    if ($relation instanceof MorphToMany) {
                        return $relation->detach($related);
                    }

                    return $related->delete();
                }, $chunk);
            }
        }
    }
}
