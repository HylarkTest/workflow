<?php

declare(strict_types=1);

namespace LighthouseHelpers\Core;

use LighthouseHelpers\Utils;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Eloquent\Builder;
use Nuwave\Lighthouse\GlobalId\GlobalIdException;

/**
 * @template TModel of \Illuminate\Database\Eloquent\Model
 */
class GlobalIdScope implements Scope
{
    /**
     * @param  \Illuminate\Database\Eloquent\Builder<TModel>  $builder
     * @param  TModel  $model
     */
    public function apply(Builder $builder, Model $model) {}

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<TModel>  $builder
     */
    public function extend(Builder $builder): void
    {
        $builder->macro('whereGlobalId', function (Builder $query, $globalIds) {
            $globalIds = \is_array($globalIds) ? $globalIds : \array_slice(\func_get_args(), 1);
            /** @var \LighthouseHelpers\GlobalIdModel $model */
            $model = $query->getModel();
            $globalIdService = $model::getGlobalIdService();
            $decodedIds = Collection::make($globalIds)->map([$globalIdService, 'decode']);

            /** @phpstan-var \Illuminate\Support\Collection<int, string> $types */
            $types = $decodedIds->pluck('0')->unique();
            /** @phpstan-var \Illuminate\Support\Collection<int, string> $ids */
            $ids = $decodedIds->pluck('1');

            if ($types->count() !== 1) {
                throw new \InvalidArgumentException('Global IDs must all reference the same type found ['.$types->implode(', ').']');
            }

            /** @var string $type */
            $type = $types->first();
            $class = Utils::namespaceModelClass($type);

            throw_if(! $class, GlobalIdException::class, 'Invalid global IDs provided');

            if (! is_a($query->getModel(), $class)) {
                throw new \InvalidArgumentException("The Global ID type [$type] must match the builder model class [".\get_class($query->getModel()).']');
            }

            return $query->whereKey($ids);
        });
    }
}
