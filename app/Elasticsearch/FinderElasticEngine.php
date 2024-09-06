<?php

declare(strict_types=1);

namespace App\Elasticsearch;

use Finder\Builder;
use Finder\Engines\ElasticEngine;
use Elastic\Adapter\Search\SearchResult;

class FinderElasticEngine extends ElasticEngine
{
    public function search(Builder $builder): mixed
    {
        $searchRequest = $this->searchRequestFactory->makeFromBuilder($builder);

        return $this->documentManager->search($searchRequest);
    }

    public function paginate(Builder $builder, int $perPage, int $page): SearchResult
    {
        $searchRequest = $this->searchRequestFactory->makeFromBuilder($builder, [
            'perPage' => $perPage,
            'page' => $page,
        ]);

        return $this->documentManager->search($searchRequest);
    }
}
