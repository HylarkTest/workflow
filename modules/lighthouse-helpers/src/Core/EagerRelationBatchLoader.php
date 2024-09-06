<?php

declare(strict_types=1);

namespace LighthouseHelpers\Core;

use GraphQL\Deferred;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class EagerRelationBatchLoader
{
    /**
     * Map from unique model keys to model instances.
     *
     * @var array<\Illuminate\Database\Eloquent\Model>
     */
    protected array $parents = [];

    /** Marks when the actual batch loading happened. */
    protected bool $hasResolved = false;

    public function __construct(
        protected array $relations,
    ) {}

    /**
     * Schedule loading a relation off of a concrete model.
     *
     * This returns effectively a promise that will resolve to
     * the result of loading the relation.
     *
     * As a side effect, the model will then hold the relation.
     */
    public function load(Model $model): Deferred
    {
        $this->parents[] = $model;

        return new Deferred(function () use ($model) {
            if (! $this->hasResolved) {
                $this->resolve();
            }

            return $model;
        });
    }

    protected function resolve(): void
    {
        $parentModels = new EloquentCollection($this->parents);

        // Monomorphize the models to simplify eager loading relations onto them
        $parentsGroupedByClass = $parentModels->groupBy(
            /**
             * @return class-string<\Illuminate\Database\Eloquent\Model>
             */
            static fn (Model $model): string => $model::class,
            true,
        );

        foreach ($parentsGroupedByClass as $parentsOfSameClass) {
            $parentsOfSameClass->load($this->relations);
        }

        $this->hasResolved = true;
    }
}
