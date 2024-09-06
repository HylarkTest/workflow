<?php

declare(strict_types=1);

namespace Tests\Finder\Integration\Engine;

use Tests\Finder\App\Client;
use Tests\Finder\App\Project;
use Elastic\Adapter\Search\Hit;
use Finder\Engines\ElasticEngine;
use Tests\Finder\Integration\TestCase;
use Illuminate\Database\Eloquent\Model;
use Elastic\Adapter\Indices\IndexManager;
use Finder\Factories\ModelFactoryInterface;
use Elastic\Adapter\Search\SearchParameters;
use Finder\Factories\RoutingFactoryInterface;
use Elastic\Adapter\Documents\DocumentManager;
use Finder\Factories\DocumentFactoryInterface;
use Finder\Factories\SearchParametersFactoryInterface;

final class EngineDeleteTest extends TestCase
{
    /**
     * @var DocumentManager
     */
    private $documentManager;

    /**
     * @test
     */
    public function empty_model_collection_can_not_be_deleted_from_index(): void
    {
        $documentManager = $this->createMock(DocumentManager::class);
        $documentManager->expects(self::never())->method('delete');

        $engine = new ElasticEngine(
            $documentManager,
            resolve(DocumentFactoryInterface::class),
            resolve(SearchParametersFactoryInterface::class),
            resolve(ModelFactoryInterface::class),
            resolve(IndexManager::class),
            resolve(RoutingFactoryInterface::class),
        );

        $engine->delete((new Client)->newCollection());
    }

    /**
     * @test
     */
    public function not_empty_model_collection_can_be_deleted_from_index(): void
    {
        $source = Client::factory(random_int(6, 10))->create();

        $deleted = $source->slice(0, mt_rand(2, 4))->each(static function (Model $client) {
            $client->forceDelete();
        });

        $searchResponse = $this->documentManager->search(
            (new SearchParameters)
                ->indices([$source->first()->globallySearchableAs()])
                ->query(['match_all' => new \stdClass])
        );

        // assert that index has less documents
        self::assertSame(
            $source->count() - $deleted->count(),
            $searchResponse->total()
        );

        // assert that index doesn't have documents with ids corresponding to the deleted models
        $documentIds = $searchResponse->hits()->map(static function (Hit $hit) {
            return $hit->document()->id();
        })->all();

        $deleted->each(function (Model $client) use ($documentIds) {
            $this->assertNotContains($client->getKey(), $documentIds);
        });
    }

    /**
     * @test
     */
    public function not_found_error_is_ignored_when_models_are_being_deleted_from_index(): void
    {
        $clients = Client::factory(random_int(2, 10))->create();

        // remove models from index
        $clients->globallyUnsearchable();

        $clients->each(function (Model $client) {
            $client->forceDelete();

            $this->assertDatabaseMissing(
                $client->getTable(),
                [$client->getKeyName() => $client->getKey()]
            );
        });
    }

    /**
     * @test
     */
    public function models_can_be_flushed_from_index(): void
    {
        $clients = Client::factory(random_int(2, 10))->create();

        Project::factory(4)->create();

        Client::removeAllFromGlobalSearch();

        $searchResponse = $this->documentManager->search(
            (new SearchParameters)
                ->indices([$clients->first()->globallySearchableAs()])
                ->query(['match_all' => new \stdClass])
        );

        // assert that index is empty
        self::assertSame(4, $searchResponse->total());
    }

    /**
     * @test
     */
    public function models_can_be_soft_deleted_from_index(): void
    {
        // enable soft deletes
        $this->app['config']->set('finder.soft_delete', true);

        $clients = Client::factory(random_int(2, 10))->create();

        $clients->each(static function (Model $client) {
            $client->delete();
        });

        $searchResponse = $this->documentManager->search(
            (new SearchParameters)
                ->indices([$clients->first()->globallySearchableAs()])
                ->query(['match_all' => new \stdClass])
        );

        $searchResponse->hits()->each(function (Hit $hit) {
            $this->assertSame(1, $hit->document()->content('__soft_deleted'));
        });
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->documentManager = resolve(DocumentManager::class);
    }
}
