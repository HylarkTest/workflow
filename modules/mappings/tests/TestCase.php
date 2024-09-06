<?php

declare(strict_types=1);

namespace Tests\Mappings;

use LighthouseHelpers\Utils;
use Illuminate\Foundation\Auth\User;
use Illuminate\Testing\TestResponse;
use Illuminate\Support\Facades\Cache;
use Mappings\MappingsServiceProvider;
use Illuminate\Testing\Assert as PHPUnit;
use Orchestra\Testbench\TestCase as BaseTestCase;
use Mappings\Core\Currency\Contracts\CurrencyRepository;
use Nuwave\Lighthouse\Schema\Source\SchemaSourceProvider;
use Tests\LighthouseHelpers\InteractsWithGraphQLExceptionHandling;

abstract class TestCase extends BaseTestCase
{
    use InteractsWithGraphQLExceptionHandling;

    protected static bool $firstTest = true;

    public function createUser($attributes = []): User
    {
        /** @var \Illuminate\Foundation\Auth\User $user */
        $user = User::query()->forceCreate(array_merge([
            'name' => 'Larry',
            'email' => 'l@r.ry',
            'password' => 'secret',
        ], $attributes));

        return $user;
    }

    /**
     * Get package providers.
     *
     * @param  \Illuminate\Foundation\Application  $app
     */
    protected function getPackageProviders($app): array
    {
        return [
            MappingsServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->handleValidationExceptions();

        $this->loadLaravelMigrations();

        $this->artisan('migrate');

        $this->app->singleton(CurrencyRepository::class, MockCurrencyRepository::class);

        TestResponse::macro('assertHasGraphQLValidationErrors', function () {
            $errors = $this->json()['errors'] ?? [];

            PHPUnit::assertNotEmpty($errors, 'Failed asserting the response has errors.');

            return $this;
        });

        config([
            'lighthouse.debug' => 15,
            'app.debug' => true,
            'lighthouse.namespaces.models' => ['Mappings\\Models'],
        ]);

        $this->app->singleton(SchemaSourceProvider::class, function (): SchemaSourceProvider {
            return new class implements SchemaSourceProvider
            {
                public function setRootPath(string $path): void {}

                public function getSchemaString(): string
                {
                    return /* @lang GraphQL */ <<<'SDL'
type Query {
    mapping(id: ID! @globalId(decode: "ID") @eq): Mapping @find
}

type Mutation {
    createMapping(mapping: MappingCreateInput! @spread): Mapping @create
    updateMapping(id: ID! @globalId(decode: "ID"), mapping: MappingUpdateInput! @spread): Mapping @update
    mapping(id: ID! @globalId(decode: "ID") @eq): MappingUpdate @find(model: "Mapping")
    deleteMapping(id: ID! @globalId(decode: "ID")): Mapping @delete(globalId: true)

    createCategory(name: String!, items: [String!]!): Category
        @field(resolver: "Tests\\Mappings\\Feature\\Categories\\CategoriesTest@resolveCreateCategory")
}
SDL;
                }
            };
        });

        Utils::clearCache();

        if (static::$firstTest) {
            Cache::flush();
            static::$firstTest = false;
        }
    }
}
