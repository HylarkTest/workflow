<?php

declare(strict_types=1);

namespace Finder\Core;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Config\Repository;

class FinderKeyResolver implements FinderKeyResolverInterface
{
    public function __construct(protected Repository $config) {}

    public function generateKey(Model $model, string $index): string
    {
        return class_basename($model).':'.$model->getKey();
    }

    public function extractClassAndIdFromKey(string $key, string $index): array
    {
        $index = Str::replaceFirst(config('finder.prefix'), '', $index);
        /** @var class-string<\Illuminate\Database\Eloquent\Model>[] $models */
        $models = (array) $this->config->get("finder.models.$index");

        [$baseName, $id] = explode(':', $key, 2);

        $model = Arr::first($models, fn (string $class): bool => class_basename($class) === $baseName);

        return [$model, $id];
    }
}
