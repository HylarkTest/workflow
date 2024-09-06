<?php

declare(strict_types=1);

namespace Finder;

use Finder\Events\ModelsFlushed;
use Finder\Events\ModelsImported;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;

/**
 * @template TModel of \Illuminate\Database\Eloquent\Model
 */
class GloballySearchableScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  EloquentBuilder<TModel>  $builder
     * @return void
     */
    public function apply(EloquentBuilder $builder, Model $model) {}

    /**
     * Extend the query builder with the needed functions.
     *
     * @param  EloquentBuilder<TModel>  $builder
     * @return void
     */
    public function extend(EloquentBuilder $builder)
    {
        $builder->macro('globallySearchable', function (EloquentBuilder $builder, $chunk = null) {
            $builder->chunkById($chunk ?: config('finder.chunk.searchable', 500), function ($models) {
                $models->filter->shouldBeGloballySearchable()->globallySearchable();

                event(new ModelsImported($models));
            });
        });

        $builder->macro('globallyUnsearchable', function (EloquentBuilder $builder, $chunk = null) {
            $builder->chunkById($chunk ?: config('finder.chunk.unsearchable', 500), function ($models) {
                $models->globallyUnsearchable();

                event(new ModelsFlushed($models));
            });
        });

        HasManyThrough::macro('globallySearchable', function ($chunk = null) {
            $this->chunkById($chunk ?: config('finder.chunk.searchable', 500), function ($models) {
                $models->filter->shouldBeGloballySearchable()->globallySearchable();

                event(new ModelsImported($models));
            });
        });

        HasManyThrough::macro('globallyUnsearchable', function ($chunk = null) {
            $this->chunkById($chunk ?: config('finder.chunk.searchable', 500), function ($models) {
                $models->globallyUnsearchable();

                event(new ModelsFlushed($models));
            });
        });
    }
}
