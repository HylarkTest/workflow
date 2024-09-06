<?php

declare(strict_types=1);

namespace Finder;

use Finder\Console\GlobalImport;
use Finder\Core\FinderKeyResolver;
use Finder\Factories\ModelFactory;
use Finder\Factories\RoutingFactory;
use Finder\Factories\DocumentFactory;
use Illuminate\Support\ServiceProvider;
use Elastic\ScoutDriverPlus\Support\Query;
use Finder\Core\FinderKeyResolverInterface;
use Finder\Factories\ModelFactoryInterface;
use Finder\Factories\RoutingFactoryInterface;
use Finder\Factories\SearchParametersFactory;
use Finder\Factories\DocumentFactoryInterface;
use Finder\Builders\MatchBoolPrefixQueryBuilder;
use Finder\Builders\DisMaxMatchPrefixQueryBuilder;
use Finder\Factories\SearchParametersFactoryInterface;

class FinderServiceProvider extends ServiceProvider
{
    /**
     * @var array<class-string, class-string>
     */
    public array $bindings = [
        FinderKeyResolverInterface::class => FinderKeyResolver::class,
        DocumentFactoryInterface::class => DocumentFactory::class,
        RoutingFactoryInterface::class => RoutingFactory::class,
        ModelFactoryInterface::class => ModelFactory::class,
        SearchParametersFactoryInterface::class => SearchParametersFactory::class,
    ];

    public function register()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                GlobalImport::class,
            ]);
        }
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/finder.php' => config_path('finder.php'),
            __DIR__.'/../elastic/migrations' => app_path('elastic/migrations'),
        ]);

        $this->mergeConfigFrom(__DIR__.'/../config/finder.php', 'finder');

        Query::macro('disMaxMatchAndPrefix', function () {
            return new DisMaxMatchPrefixQueryBuilder;
        });

        Query::macro('matchBoolPrefix', function () {
            return new MatchBoolPrefixQueryBuilder;
        });
    }
}
