<?php

declare(strict_types=1);

namespace Planner;

use Planner\Events\TodoCompleted;
use Illuminate\Support\ServiceProvider;
use Nuwave\Lighthouse\Schema\TypeRegistry;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Events\Dispatcher;
use Planner\Listeners\MoveToNextRecurrence;
use LighthouseHelpers\GraphQLServiceProvider;
use Nuwave\Lighthouse\Events\RegisterDirectiveNamespaces;

class PlannerServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     */
    public function boot(Dispatcher $events, Repository $config, TypeRegistry $typeRegistry): void
    {
        $this->loadResources();

        $this->bootGraphQl($events, $config, $typeRegistry);

        $events->listen(TodoCompleted::class, MoveToNextRecurrence::class);
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->register(GraphQLServiceProvider::class);
    }

    protected function loadResources(): void
    {
        $this->publishes([
            __DIR__.'/../config/planner.php' => config_path('planner.php'),
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ]);

        $this->mergeConfigFrom(__DIR__.'/../config/planner.php', 'planner');

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations/');
    }

    protected function bootGraphQl(Dispatcher $events, Repository $config, TypeRegistry $typeRegistry): void
    {
        $modelNamespaces = (array) $config->get('lighthouse.namespaces.models');
        $modelNamespaces[] = 'Planner\\Models';
        $config->set('lighthouse.namespaces.models', $modelNamespaces);

        $events->listen(
            RegisterDirectiveNamespaces::class,
            fn () => 'Planner\\GraphQL\\Directives'
        );
    }
}
