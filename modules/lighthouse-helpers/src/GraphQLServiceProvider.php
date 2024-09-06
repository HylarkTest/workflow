<?php

declare(strict_types=1);

namespace LighthouseHelpers;

use Color\ColorFormat;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;
use Lampager\Laravel\MacroServiceProvider;
use LighthouseHelpers\Core\NativeEnumType;
use Nuwave\Lighthouse\Schema\TypeRegistry;
use GraphQL\Executor\Promise\PromiseAdapter;
use Nuwave\Lighthouse\Auth\AuthServiceProvider;
use Illuminate\Contracts\Translation\Translator;
use Nuwave\Lighthouse\LighthouseServiceProvider;
use Nuwave\Lighthouse\Cache\CacheServiceProvider;
use Nuwave\Lighthouse\OrderBy\OrderByServiceProvider;
use Nuwave\Lighthouse\Testing\TestingServiceProvider;
use Nuwave\Lighthouse\GlobalId\GlobalIdServiceProvider;
use GraphQL\Executor\Promise\Adapter\SyncPromiseAdapter;
use Nuwave\Lighthouse\Pagination\PaginationServiceProvider;
use Nuwave\Lighthouse\Validation\ValidationServiceProvider;
use Nuwave\Lighthouse\SoftDeletes\SoftDeletesServiceProvider;

class GraphQLServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @throws \Nuwave\Lighthouse\Exceptions\DefinitionException
     */
    public function boot(TypeRegistry $typeRegistry, Translator $translator): void
    {
        $typeRegistry->register(new NativeEnumType(ColorFormat::class));

        $this->publishes([
            __DIR__.'/../lang' => resource_path('lang/vendor/lighthouse-helpers'),
        ]);

        $this->loadTranslationsFrom(__DIR__.'/../lang', 'lighthouse-helpers');

        Validator::extend('api_name', static function (string $attribute, $value) {
            return \is_string($value) && preg_match('/^[_a-zA-Z][a-zA-Z0-9]*/', $value);
        }, $translator->get('lighthouse-helpers::validation.rules.api_name'));
    }

    public function register()
    {
        $this->app->register(OrderByServiceProvider::class);
        $this->app->register(PostLighthouseServiceProvider::class);
        $this->app->register(LighthouseServiceProvider::class);
        $this->app->register(GlobalIdServiceProvider::class);
        $this->app->register(PaginationServiceProvider::class);
        $this->app->register(ValidationServiceProvider::class);
        $this->app->register(MacroServiceProvider::class);
        $this->app->register(AuthServiceProvider::class);
        $this->app->register(SoftDeletesServiceProvider::class);
        $this->app->register(CacheServiceProvider::class);
        $this->app->register(TestingServiceProvider::class);

        $this->app->singleton(TypeRegistry::class, Core\TypeRegistry::class);

        $this->app->bind(PromiseAdapter::class, SyncPromiseAdapter::class);
    }
}
