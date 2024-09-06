<?php

declare(strict_types=1);

namespace LighthouseHelpers\Core;

use Illuminate\Database\Eloquent\Model;
use Nuwave\Lighthouse\Execution\ModelsLoader\ModelsLoader;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

/**
 * @template TModel of \Illuminate\Database\Eloquent\Model
 */
class LoaderDecorator implements ModelsLoader
{
    public function __construct(protected ModelsLoader $loader, protected \Closure $customExtraction) {}

    /**
     * @param  \Illuminate\Database\Eloquent\Collection<int, TModel>  $parents
     */
    public function load(EloquentCollection $parents): void
    {
        $this->loader->load($parents);
    }

    /**
     * @param  TModel  $model
     */
    public function extract(Model $model): mixed
    {
        $customExtraction = $this->customExtraction;

        return $customExtraction($this->loader->extract($model));
    }
}
