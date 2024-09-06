<?php

declare(strict_types=1);

namespace Tests\Finder\Integration\Engine;

use Finder\Engines\ElasticEngine;
use Tests\Finder\Integration\TestCase;
use Elastic\Adapter\Indices\IndexManager;

/**
 * @covers \Elastic\ScoutDriver\Engine
 */
final class EngineIndexTest extends TestCase
{
    private const INDEX_NAME = 'test';

    private IndexManager $indexManager;

    private ElasticEngine $engine;

    /**
     * @test
     */
    public function index_with_given_name_can_be_created(): void
    {
        $this->engine->createIndex(self::INDEX_NAME);
        self::assertTrue($this->indexManager->exists(self::INDEX_NAME));
    }

    /**
     * @depends index_with_given_name_can_be_created
     *
     * @test
     */
    public function index_can_be_deleted_by_name(): void
    {
        $this->engine->deleteIndex(self::INDEX_NAME);
        self::assertFalse($this->indexManager->exists(self::INDEX_NAME));
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->indexManager = resolve(IndexManager::class);
        $this->engine = resolve(ElasticEngine::class);
    }
}
