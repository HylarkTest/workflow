<?php

declare(strict_types=1);

namespace App\Elasticsearch;

use Laravel\Scout\Builder;
use Elastic\Adapter\Search\SearchParameters;
use Elastic\ScoutDriver\Factories\SearchParametersFactory;

class ScoutSearchParametersFactory extends SearchParametersFactory
{
    public function makeFromBuilder(Builder $builder, array $options = []): SearchParameters
    {
        if (should_be_scoped($builder->model)) {
            $base = tenancy()->tenant;

            if (! $base) {
                throw new \Exception('Cannot search without a tenant');
            }
            $builder->where('_routing', $base->getKey());

            $request = parent::makeFromBuilder($builder, $options);

            $request->preference((string) $base->getKey());

            return $request;
        }

        return parent::makeFromBuilder($builder, $options);
    }
}
