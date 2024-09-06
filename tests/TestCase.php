<?php

declare(strict_types=1);

namespace Tests;

use GraphQL\Deferred;
use App\Console\Kernel;
use Illuminate\Support\Str;
use LighthouseHelpers\Utils;
use Nuwave\Lighthouse\GraphQL;
use Illuminate\Support\Facades\DB;
use Illuminate\Testing\TestResponse;
use Illuminate\Support\Facades\Cache;
use Tests\Concerns\UsesElasticsearch;
use Illuminate\Testing\Assert as PHPUnit;
use App\GraphQL\AST\BuildCustomFieldTypes;
use Nuwave\Lighthouse\Schema\TypeRegistry;
use Tests\Mappings\MockCurrencyRepository;
use Nuwave\Lighthouse\Schema\SchemaBuilder;
use Nuwave\Lighthouse\Schema\AST\ASTBuilder;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;
use Nuwave\Lighthouse\Testing\RefreshesSchemaCache;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Mappings\Core\Currency\Contracts\CurrencyRepository;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Tests\LighthouseHelpers\InteractsWithGraphQLExceptionHandling;

/**
 * @method beginDatabaseTransaction()
 */
abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    use InteractsWithGraphQLExceptionHandling;
    use MakesGraphQLRequests;
    use RefreshesSchemaCache;

    protected static bool $firstTest = true;

    /**
     * Accepts an expected query response and builds a GraphQL query from it,
     * asserting that the response matches the provided data.
     *
     * The expected query can instead just be a path string for a very simple query.
     * In which case it will not make a comparison with the response, just assert that the query is valid.
     *
     * @param  array  $expected
     * @param  mixed  $succeeded
     */
    public function assertGraphQL(array|string $expected, array $args = [], string $root = 'query', $succeeded = true): TestResponse
    {
        $argNames = [];
        $input = [];
        foreach ($args as $key => $value) {
            $argNames[] = "$$key";
            $input[strtok($key, ':')] = $value;
        }

        if (\is_string($expected)) {
            $expected = explode('.', $expected);
        }

        if (array_is_list($expected)) {
            if (\count($expected) === 1) {
                $expected[] = 'code';
                if (! Str::contains($expected[0], '(')) {
                    $expected[0] = $expected[0].'(input: $input)';
                } elseif (Str::contains($expected[0], '()')) {
                    $expected[0] = str_replace('()', '', $expected[0]);

                }
            }
            $query = '{ '.array_reduce(
                array_reverse($expected),
                fn ($carry, $item) => $carry ? "$item { $carry }" : $item,
                ''
            ).' }';
            $expected = null;
        } else {
            $query = buildQueryFromExpectedResponse($expected);
        }

        if ($argNames) {
            $query = $root.' ('.implode(', ', $argNames).') '.$query;
        } else {
            $query = $root.' '.$query;
        }

        $response = convertToFileRequest($query, $input);

        if ($succeeded) {
            $response->assertSuccessfulGraphQL();
            if ($expected) {
                $response->assertJson(['data' => clearArgumentsAndFragments($expected, $response->json('data'))], true);
            }
        } else {
            static::assertArrayHasKey('errors', $response->json());
        }

        return $response;
    }

    public function assertGraphQLMutation(array|string $expected = [], array $args = []): TestResponse
    {
        return $this->assertGraphQL($expected, $args, 'mutation');
    }

    public function assertFailedGraphQLMutation(array|string $expected = [], array $args = []): TestResponse
    {
        return $this->assertGraphQL($expected, $args, 'mutation', false);
    }

    protected function refreshTestDatabase()
    {
        if (! RefreshDatabaseState::$migrated) {
            DB::unprepared(file_get_contents(database_path('schema.sql')));

            $this->artisan('migrate');

            $this->app[Kernel::class]->setArtisan(null);

            RefreshDatabaseState::$migrated = true;
        }

        $this->beginDatabaseTransaction();
    }

    protected function setUpTraits(): void
    {
        $uses = parent::setUpTraits();

        if (isset($uses[UsesElasticsearch::class]) && \in_array('es', $this->groups(), true)) {
            $this->setUpElasticsearch();
        }
    }

    protected function resolveDeferred(mixed $value): mixed
    {
        if ($value instanceof Deferred) {
            Deferred::runQueue();

            return $value->result;
        }
        if (\is_array($value)) {
            $resolvedValue = [];
            foreach ($value as $key => $val) {
                $resolvedValue[$key] = $this->resolveDeferred($val);
            }

            return $resolvedValue;
        }

        return $value;
    }

    protected function forgetLighthouseClasses(): void
    {
        app()->forgetInstance(GraphQL::class);
        app()->forgetInstance(SchemaBuilder::class);
        app()->forgetInstance(ASTBuilder::class);
        app()->forgetInstance(TypeRegistry::class);
        resolve(BuildCustomFieldTypes::class)->build();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->handleValidationExceptions();

        if (static::$firstTest) {
            Cache::flush();
            static::$firstTest = false;
        }

        Utils::clearCache();

        TestResponse::macro('assertHasGraphQLValidationErrors', function () {
            $errors = $this->json()['errors'] ?? [];

            PHPUnit::assertNotEmpty($errors, 'Failed asserting the response has errors.');

            return $this;
        });

        TestResponse::macro('assertSuccessfulGraphQL', function () {
            $errors = $this->json()['errors'] ?? [];

            PHPUnit::assertEmpty($errors, 'Failed asserting the response was successful.');

            return $this;
        });

        TestResponse::macro('assertGraphQLMissing', function () {
            $errors = $this->json()['errors'] ?? [];

            PHPUnit::assertSame('missing', $errors[0]['extensions']['category'] ?? null, 'Failed asserting the response indicates a missing resource.');

            return $this;
        });

        TestResponse::macro('assertGraphQLUnauthorized', function () {
            $errors = $this->json()['errors'] ?? [];

            PHPUnit::assertSame('unauthorized', $errors[0]['extensions']['category'] ?? null, 'Failed asserting the response indicates an unauthorized request.');

            return $this;
        });

        $this->app->singleton(CurrencyRepository::class, MockCurrencyRepository::class);
        config([
            'actions.automatic' => false,
            'tenancy.filesystem.suffix_base' => 'framework/testing/base'.getenv('TEST_TOKEN'),
        ]);
    }

    protected function tearDown(): void
    {
        $uses = array_flip(class_uses_recursive(static::class));

        if (isset($uses[UsesElasticsearch::class]) && \in_array('es', $this->groups(), true)) {
            $this->tearDownElasticsearch();
        }

        parent::tearDown();
    }
}
