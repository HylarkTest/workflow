<?php

declare(strict_types=1);

namespace App\Elasticsearch;

use Finder\Builder;
use Elastic\Adapter\Search\SearchParameters;
use Finder\Factories\SearchParametersFactory;

class FinderSearchParametersFactory extends SearchParametersFactory
{
    public function makeFromBuilder(Builder $builder, array $options = []): SearchParameters
    {
        $base = tenancy()->tenant;

        if (! $base) {
            throw new \Exception('Cannot search without a tenant');
        }

        $builder->where('_routing', $base->getKey());

        $request = parent::makeFromBuilder($builder, $options);

        $request->preference((string) $base->getKey());

        return $request;
    }
}
