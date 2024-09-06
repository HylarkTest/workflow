<?php

declare(strict_types=1);

namespace App\Elasticsearch;

use Elastic\Adapter\Client;
use Elastic\ScoutDriverPlus\Builders\QueryBuilderInterface;

class QueryUpdateEngine
{
    use Client;

    public function updateByQuery(string $indexName, array|QueryBuilderInterface $query, array $fields): void
    {
        if (config('scout.driver') !== 'elastic') {
            return;
        }
        $params = [
            'index' => $indexName,
            'body' => [
                'query' => $query instanceof QueryBuilderInterface ? $query->buildQuery() : $query,
                'script' => [
                    'source' => 'for (field in params.fields.keySet()) { ctx._source[field] = params.fields[field] }',
                    'params' => ['fields' => $fields],
                    'lang' => 'painless',
                ],
            ],
            'routing' => tenant('id'),
            'conflicts' => 'proceed',
        ];

        $this->client->updateByQuery($params);
    }
}
