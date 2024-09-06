<?php

declare(strict_types=1);

namespace Timekeeper;

use Illuminate\Support\ServiceProvider;
use Nuwave\Lighthouse\Schema\TypeRegistry;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Events\Dispatcher;
use LighthouseHelpers\GraphQLServiceProvider;

class TimekeeperServiceProvider extends ServiceProvider
{
    public function boot(Dispatcher $events, Repository $config, TypeRegistry $typeRegistry): void
    {
        $this->loadResources();
    }

    public function register(): void
    {
        $this->app->register(GraphQLServiceProvider::class);
    }

    protected function loadResources(): void
    {
        $this->publishes([
            __DIR__.'/../config/timekeeper.php' => config_path('timekeeper.php'),
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ]);

        $this->mergeConfigFrom(__DIR__.'/../config/timekeeper.php', 'timekeeper');

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations/');
    }
}
