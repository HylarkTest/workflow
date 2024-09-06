<?php

declare(strict_types=1);

namespace App\GraphQL;

use Illuminate\Support\Arr;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Nuwave\Lighthouse\Execution\ModelsLoader\CountModelsLoader as BaseCountModelsLoader;

/**
 * Because of the way loading count works in laravel, we need to override here
 * to ensure the query includes the base_id
 */
class CountModelsLoader extends BaseCountModelsLoader
{
    /**
     * @param  \Illuminate\Database\Eloquent\Collection<int, \Illuminate\Database\Eloquent\Model>  $parents
     */
    public function load(EloquentCollection $parents): void
    {
        static::loadCount($parents, [$this->relation => $this->decorateBuilder]);
    }

    /**
     * Reload the models to get the `{relation}_count` attributes of models set.
     *
     * @deprecated Laravel 5.7 has native ->loadCount() on EloquentCollection
     * @see \Illuminate\Database\Eloquent\Collection::loadCount()
     *
     * @param  \Illuminate\Database\Eloquent\Collection<int, \Illuminate\Database\Eloquent\Model>  $parents
     * @param  array<string, \Closure>  $relations
     */
    public static function loadCount(EloquentCollection $parents, array $relations): void
    {
        $firstParent = $parents->first();
        if (! $firstParent) {
            return;
        }

        $query = $firstParent->newModelQuery()
            ->whereKey($parents->modelKeys())
            ->select($firstParent->getKeyName())
            ->withCount($relations);

        if (should_be_scoped($firstParent)) {
            $query->where('base_id', tenancy()->tenant?->getKey());
        }

        $models = $query->get()
            ->keyBy($firstParent->getKeyName());

        $firstModel = $models->first();
        \assert($firstModel instanceof Model);
        $attributes = Arr::except(
            array_keys($firstModel->getAttributes()),
            $firstModel->getKeyName()
        );

        foreach ($parents as $parent) {
            $model = $models->get($parent->getKey());
            \assert($model instanceof Model);

            $extraAttributes = Arr::only($model->getAttributes(), $attributes);

            $parent->forceFill($extraAttributes);

            foreach ($attributes as $attribute) {
                $parent->syncOriginalAttribute($attribute);
            }
        }
    }
}
