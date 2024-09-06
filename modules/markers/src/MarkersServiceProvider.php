<?php

declare(strict_types=1);

namespace Markers;

use Markers\Core\MarkerType;
use Illuminate\Support\ServiceProvider;
use LighthouseHelpers\Core\NativeEnumType;
use Nuwave\Lighthouse\Schema\TypeRegistry;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Events\Dispatcher;
use LighthouseHelpers\GraphQLServiceProvider;
use Nuwave\Lighthouse\Events\RegisterDirectiveNamespaces;

class MarkersServiceProvider extends ServiceProvider
{
    public function boot(Dispatcher $events, Repository $config, TypeRegistry $typeRegistry): void
    {
        $this->loadResources();

        $this->bootGraphQl($events, $config, $typeRegistry);
    }

    public function register(): void
    {
        $this->app->register(GraphQLServiceProvider::class);
    }

    protected function loadResources(): void
    {
        $this->publishes([
            __DIR__.'/../config/markers.php' => config_path('markers.php'),
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ]);

        $this->mergeConfigFrom(__DIR__.'/../config/markers.php', 'markers');

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations/');
    }

    protected function bootGraphQl(Dispatcher $events, Repository $config, TypeRegistry $typeRegistry): void
    {
        $modelNamespaces = (array) $config->get('lighthouse.namespaces.models');
        $modelNamespaces[] = 'Markers\\Models';
        $config->set('lighthouse.namespaces.models', $modelNamespaces);

        $mutationNamespaces = (array) $config->get('lighthouse.namespaces.mutations');
        $mutationNamespaces[] = 'Markers\\GraphQL\\Mutations';
        $config->set('lighthouse.namespaces.mutations', $mutationNamespaces);

        $events->listen(
            RegisterDirectiveNamespaces::class,
            fn () => 'Markers\\GraphQL\\Directives'
        );

        $typeRegistry->register(new NativeEnumType(MarkerType::class));
    }
}
