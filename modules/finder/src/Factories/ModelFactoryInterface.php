<?php

declare(strict_types=1);

namespace Finder\Factories;

use Finder\Builder;
use Illuminate\Support\Collection;
use Elastic\Adapter\Search\SearchResult;

interface ModelFactoryInterface
{
    /**
     * @return \Illuminate\Support\Collection<int, \Illuminate\Database\Eloquent\Model&\Finder\GloballySearchable>
     */
    public function makeFromSearchResponse(
        SearchResult $searchResult,
        Builder $builder
    ): Collection;
}
