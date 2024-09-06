<?php

declare(strict_types=1);

namespace Finder\Engines;

use Finder\Builder;
use Finder\GloballySearchable;
use Elastic\Adapter\Search\Hit;
use Elastic\Adapter\Indices\Index;
use Illuminate\Support\Collection;
use Elastic\Adapter\Search\SearchResult;
use Elastic\Adapter\Indices\IndexManager;
use Finder\Factories\ModelFactoryInterface;
use Finder\Factories\RoutingFactoryInterface;
use Elastic\Adapter\Documents\DocumentManager;
use Finder\Factories\DocumentFactoryInterface;
use Finder\Factories\SearchParametersFactoryInterface;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class ElasticEngine extends Engine
{
    protected bool $refreshDocuments;

    public function __construct(
        protected DocumentManager $documentManager,
        protected DocumentFactoryInterface $documentFactory,
        protected SearchParametersFactoryInterface $searchRequestFactory,
        protected ModelFactoryInterface $modelFactory,
        protected IndexManager $indexManager,
        protected RoutingFactoryInterface $routingFactory,
    ) {
        $this->refreshDocuments = config('elastic.scout_driver.refresh_documents');
    }

    public function update(EloquentCollection $models): void
    {
        if ($models->isEmpty()) {
            return;
        }

        $models->groupBy(fn (GloballySearchable $model): string => $model->globallySearchableAs())
            ->each(function (EloquentCollection $models, string $index) {
                $routing = $this->routingFactory->makeFromModels($models);
                $documents = $this->documentFactory->makeFromModels($models);

                $this->documentManager->index($index, $documents, $this->refreshDocuments, $routing);
            });
    }

    public function delete(EloquentCollection $models): void
    {
        if ($models->isEmpty()) {
            return;
        }

        $models->groupBy(fn (GloballySearchable $model): string => $model->globallySearchableAs())
            ->each(function (Collection $models, string $index) {
                $routing = $this->routingFactory->makeFromModels($models);
                $documentIds = $models->map(static function (GloballySearchable $model): string {
                    return $model->getFinderKey();
                })->all();

                $this->documentManager->delete($index, $documentIds, $this->refreshDocuments, $routing);
            });
    }

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

    public function cursorPaginate(Builder $builder, int $perPage, ?array $cursor): SearchResult
    {
        $index = $builder->index;

        $searchRequest = $this->searchRequestFactory->makeFromBuilder($builder, [
            'perPage' => $perPage,
        ]);

        if ($cursor) {
            $searchRequest->searchAfter($cursor);
        }

        $searchRequest->highlight([
            'fields' => [
                'primary.text' => new \stdClass,
                'secondary.text' => new \stdClass,
            ],
            'encoder' => 'html',
        ]);

        $searchRequest->indices([$index]);

        return $this->documentManager->search($searchRequest);
    }

    public function mapIds(mixed $results): Collection
    {
        return $results->hits()->map(static function (Hit $hit) {
            return $hit->document()->id();
        });
    }

    /**
     * Map the given results to instances of the given model.
     *
     * @param  \Elastic\Adapter\Search\SearchResult  $results
     * @return \Illuminate\Support\Collection<int, \Illuminate\Database\Eloquent\Model&\Finder\GloballySearchable>
     */
    public function map(Builder $builder, mixed $results): Collection
    {
        return $this->modelFactory->makeFromSearchResponse($results, $builder);
    }

    /**
     * @param  \Elastic\Adapter\Search\SearchResult  $results
     */
    public function getTotalCount($results): ?int
    {
        return $results->total();
    }

    public function flush(GloballySearchable $model): void
    {
        $index = $model->globallySearchableAs();
        $query = ['term' => ['__typename' => $model->finderTypename()]];

        $this->documentManager->deleteByQuery($index, $query, $this->refreshDocuments);
    }

    public function createIndex(string $name, array $options = []): void
    {
        if (isset($options['primaryKey'])) {
            throw new \InvalidArgumentException('It is not possible to change the primary key name');
        }

        $this->indexManager->create(new Index($name));
    }

    public function deleteIndex(string $name): void
    {
        $this->indexManager->drop($name);
    }
}
