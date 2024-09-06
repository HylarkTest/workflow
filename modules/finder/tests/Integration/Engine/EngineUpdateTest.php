<?php

declare(strict_types=1);

namespace Tests\Finder\Integration\Engine;

use Tests\Finder\App\Client;
use Elastic\Adapter\Search\Hit;
use Finder\Engines\ElasticEngine;
use Tests\Finder\Integration\TestCase;
use Elastic\Adapter\Indices\IndexManager;
use Finder\Core\FinderKeyResolverInterface;
use Finder\Factories\ModelFactoryInterface;
use Elastic\Adapter\Search\SearchParameters;
use Finder\Factories\RoutingFactoryInterface;
use Elastic\Adapter\Documents\DocumentManager;
use Finder\Factories\DocumentFactoryInterface;
use Finder\Factories\SearchParametersFactoryInterface;

/**
 * @covers \Elastic\ScoutDriver\Engine
 *
 * @uses   \Elastic\ScoutDriver\Factories\DocumentFactory
 */
final class EngineUpdateTest extends TestCase
{
    /**
     * @var DocumentManager
     */
    private $documentManager;

    /**
     * @test
     */
    public function empty_model_collection_can_not_be_indexed(): void
    {
        $documentManager = $this->createMock(DocumentManager::class);
        $documentManager->expects(self::never())->method('index');

        $engine = new ElasticEngine(
            $documentManager,
            resolve(DocumentFactoryInterface::class),
            resolve(SearchParametersFactoryInterface::class),
            resolve(ModelFactoryInterface::class),
            resolve(IndexManager::class),
            resolve(RoutingFactoryInterface::class),
        );

        $engine->update((new Client)->newCollection());
    }

    /**
     * @test
     */
    public function not_empty_model_collection_can_be_indexed(): void
    {
        $clients = Client::factory(random_int(2, 10))->create();

        $index = $clients->first()->globallySearchableAs();

        $searchResponse = $this->documentManager->search(
            (new SearchParameters)
                ->indices([$index])
                ->query(['match_all' => new \stdClass])
        );

        // assert that the amount of created models corresponds number of found documents
        self::assertSame($clients->count(), $searchResponse->total());

        // assert that the same model ids are in the index
        $clientIds = $clients->pluck($clients->first()->getKeyName())->all();

        $documentIds = $searchResponse->hits()->map(static function (Hit $hit) use ($index) {
            return (int) resolve(FinderKeyResolverInterface::class)->extractClassAndIdFromKey($hit->document()->id(), $index)[1];
        })->all();

        self::assertSame($clientIds, $documentIds);
    }

    /**
     * @test
     */
    public function metadata_is_indexed_when_soft_deletes_are_enabled(): void
    {
        // enable soft deletes
        $this->app['config']->set('finder.soft_delete', true);

        $clients = Client::factory(random_int(2, 10))->create();

        $searchResponse = $this->documentManager->search(
            (new SearchParameters)
                ->indices([$clients->first()->globallySearchableAs()])
                ->query(['match_all' => new \stdClass])
        );

        $searchResponse->hits()->each(function (Hit $hit) {
            $this->assertSame(0, $hit->document()->content('__soft_deleted'));
        });
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->documentManager = resolve(DocumentManager::class);
    }
}
