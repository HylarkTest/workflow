<?php

declare(strict_types=1);

namespace Tests\Finder\Integration\Engine;

use Finder\Finder;
use Illuminate\Support\Arr;
use Tests\Finder\App\Client;
use Tests\Finder\App\Project;
use Tests\Finder\Integration\TestCase;
use Elastic\Adapter\Search\SearchResult;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * @covers \Elastic\ScoutDriver\Engine
 *
 * @uses   \Elastic\ScoutDriver\Factories\DocumentFactory
 * @uses   \Elastic\ScoutDriver\Factories\ModelFactory
 * @uses   \Elastic\ScoutDriver\Factories\SearchRequestFactory
 */
final class EngineSearchTest extends TestCase
{
    /**
     * @test
     */
    public function ids_can_be_retrieved_from_search_result(): void
    {
        $clients = Client::factory(random_int(2, 10))->create();
        $projects = Project::factory(random_int(2, 10))->create();

        $found = Finder::search()->take(20)->keys()->sort();

        self::assertSame(
            $clients->map->getFinderKey()->merge(
                $projects->map->getFinderKey()
            )->sort()->values()->all(),
            $found->values()->all()
        );
    }

    /**
     * @test
     */
    public function all_models_can_be_found(): void
    {
        $clients = Client::factory(random_int(2, 10))->create();
        $projects = Project::factory(random_int(2, 10))->create();
        $source = $clients->toBase()->merge($projects);
        $found = Finder::search()->take(20)->get();

        self::assertSame($source->count(), $found->count());
    }

    /**
     * @test
     */
    public function models_corresponding_query_string_can_be_found(): void
    {
        Client::factory(random_int(2, 10))->create();
        Project::factory(random_int(2, 10))->create();

        $targetClient = Client::factory()->create(['name' => uniqid('John ', true)]);
        $targetProject = Project::factory()->create(['name' => uniqid('John ', true)]);
        $found = Finder::search('John')->get();

        self::assertCount(2, $found);
        self::assertSame($targetClient->getKey(), $found->first()->getKey());
        self::assertSame($targetProject->getKey(), $found->last()->getKey());
    }

    /**
     * @test
     */
    public function search_result_can_be_filtered_with_where_clause(): void
    {
        Client::factory(random_int(2, 10))->create();
        Project::factory(random_int(2, 10))->create();

        $targetClient = Client::factory()->create(['name' => uniqid('John ', true)]);
        Project::factory()->create(['name' => uniqid('John ', true)]);
        $found = Finder::search('John')->where('__typename', 'Client')->get();

        self::assertCount(1, $found);
        self::assertSame($targetClient->getKey(), $found->first()->getKey());
    }

    /**
     * @test
     */
    public function search_result_can_be_sorted(): void
    {
        $source = Client::factory(random_int(2, 10))->create()->sortBy('name')->values();
        $found = Finder::search()->orderBy('primary.text.keyword')->get();

        foreach ($found as $key => $result) {
            self::assertTrue($result->is($source->get($key)));
        }
    }

    /**
     * @test
     */
    public function search_result_can_be_limited(): void
    {
        Client::factory(random_int(10, 20))->create();

        $found = Finder::search()->take(5)->get();

        self::assertCount(5, $found);
    }

    /**
     * @test
     */
    public function search_result_can_be_paginated(): void
    {
        // add some mixins
        Client::factory(6)->create();

        $target = Client::factory(5)
            ->create(['name' => uniqid('John ', true)])
            ->sortBy('name')
            ->values();

        /** @var LengthAwarePaginator $paginator */
        $paginator = Finder::search($target->first()->name)
            ->orderBy('primary.text.keyword', 'asc')
            ->paginate(2, 'p', 3);

        self::assertSame(2, $paginator->perPage());
        self::assertSame('p', $paginator->getPageName());
        self::assertSame(3, $paginator->currentPage());
        self::assertSame(5, $paginator->total());
        self::assertCount(1, $paginator->items());
        self::assertSame($target->last()->fresh()->toArray(), Arr::except($paginator[0]->toArray(), 'cursor'));
    }

    /**
     * Search results can be cursor paginated
     *
     * @test
     */
    public function search_results_can_be_cursor_paginated(): void
    {
        // add some mixins
        Client::factory(6)->create();

        $target = Client::factory(5)
            ->create(fn () => ['name' => uniqid('John ', true)])
            ->sortBy('name')
            ->values();

        /** @var \Lampager\Laravel\PaginationResult $paginator */
        $paginator = Finder::search($target->first()->name)
            ->orderBy('primary.text.keyword', 'asc')
            ->orderBy('id', 'asc')
            ->cursorPaginate(4);

        self::assertTrue($paginator->hasNext);
        self::assertFalse($paginator->hasPrevious);
        self::assertSame(5, $paginator->total);
        self::assertSame($paginator->nextCursor, $paginator->last()->cursor);

        /** @var \Lampager\Laravel\PaginationResult $paginator */
        $paginator = Finder::search($target->first()->name)
            ->orderBy('primary.text.keyword', 'asc')
            ->orderBy('id', 'asc')
            ->cursorPaginate(4, $paginator->nextCursor);

        self::assertTrue($paginator->hasPrevious);
        self::assertFalse($paginator->hasNext);
        self::assertCount(1, $paginator->all());
        self::assertSame($target->last()->fresh()->toArray(), Arr::except($paginator->get(0)->toArray(), ['cursor', 'pivot']));
    }

    /**
     * @test
     */
    public function raw_search_returns_instance_of_search_response(): void
    {
        $source = Client::factory(random_int(2, 10))->create();
        $foundRaw = Finder::search()->raw();

        self::assertInstanceOf(SearchResult::class, $foundRaw);
        self::assertSame($source->count(), $foundRaw->total());
    }

    /**
     * @test
     */
    public function soft_deleted_models_are_not_included_in_search_result(): void
    {
        // enable soft deletes
        $this->app['config']->set('finder.soft_delete', true);

        Client::factory(random_int(2, 10))->create(['deleted_at' => now()]);

        $found = Finder::search()->get();

        self::assertCount(0, $found);
    }

    /**
     * @test
     */
    public function mini_language_syntax_can_be_used_in_query_string(): void
    {
        foreach (['Stan', 'John', 'Matthew'] as $name) {
            Client::factory()->create(compact('name'));
        }

        $found = Finder::search('primary:(John OR Matthew)')->get();

        self::assertSame(['John', 'Matthew'], $found->pluck('name')->all());
    }
}
