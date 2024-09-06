<?php

declare(strict_types=1);

namespace Documents;

use Documents\Core\FileType;
use Illuminate\Support\ServiceProvider;
use LighthouseHelpers\Core\NativeEnumType;
use Nuwave\Lighthouse\Schema\TypeRegistry;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Events\Dispatcher;
use LighthouseHelpers\GraphQLServiceProvider;
use Nuwave\Lighthouse\Events\RegisterDirectiveNamespaces;

class DocumentsServiceProvider extends ServiceProvider
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
            __DIR__.'/../config/documents.php' => config_path('documents.php'),
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ]);

        $this->mergeConfigFrom(__DIR__.'/../config/documents.php', 'documents');

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations/');
    }

    protected function bootGraphQl(Dispatcher $events, Repository $config, TypeRegistry $typeRegistry): void
    {
        $modelNamespaces = (array) $config->get('lighthouse.namespaces.models');
        $modelNamespaces[] = 'Documents\\Models';
        $config->set('lighthouse.namespaces.models', $modelNamespaces);

        $events->listen(
            RegisterDirectiveNamespaces::class,
            fn () => 'Documents\\GraphQL\\Directives'
        );

        $typeRegistry->register(new NativeEnumType(FileType::class));
    }
}
