<?php

declare(strict_types=1);

namespace Tests\Concerns;

use Elastic\Elasticsearch\Client;
use Elastic\Adapter\Indices\IndexManager;
use Elastic\Client\ClientBuilderInterface;
use Elastic\Migrations\Console\MigrateCommand;

/**
 * @mixin \Tests\TestCase
 */
trait UsesElasticsearch
{
    public function esClient(): Client
    {
        return resolve(ClientBuilderInterface::class)->default();
    }

    public function refreshIndex(string $index = '*'): void
    {
        $prefix = $this->prefix();
        $this->esClient()->indices()->refresh(['index' => $prefix.$index]);
    }

    protected function setUpElasticsearch(): void
    {
        $prefix = $this->prefix();
        config([
            'finder.driver' => 'elastic',
            'scout.driver' => 'elastic',
            'finder.prefix' => $prefix,
            'scout.prefix' => $prefix,
        ]);
        $this->artisan(MigrateCommand::class, ['--force' => true])->run();
    }

    protected function tearDownElasticsearch(): void
    {
        resolve(IndexManager::class)->drop($this->prefix().'*');
    }

    protected function prefix(): string
    {
        return 'test'.getenv('TEST_TOKEN').'-';
    }
}
