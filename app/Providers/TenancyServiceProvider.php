<?php

declare(strict_types=1);

namespace App\Providers;

use Stancl\Tenancy\Events;
use Stancl\Tenancy\Listeners;
use Stancl\Tenancy\Middleware;
use Illuminate\Events\Dispatcher;
use Stancl\JobPipeline\JobPipeline;
use Stancl\Tenancy\Contracts\Tenant;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use App\Elasticsearch\ScoutRoutingFactory;
use App\Elasticsearch\FinderRoutingFactory;
use Illuminate\Contracts\Database\Query\Builder;
use App\Elasticsearch\ScoutSearchParametersFactory;
use App\Elasticsearch\FinderSearchParametersFactory;
use Finder\Factories\RoutingFactoryInterface as FinderRoutingFactoryInterface;
use Finder\Factories\SearchParametersFactoryInterface as FinderSearchRequestFactoryInterface;
use Elastic\ScoutDriverPlus\Factories\RoutingFactoryInterface as ScoutRoutingFactoryInterface;
use Elastic\ScoutDriver\Factories\SearchParametersFactoryInterface as ScoutSearchParametersFactoryInterfaceAlias;

class TenancyServiceProvider extends ServiceProvider
{
    // By default, no namespace is used to support the callable array syntax.
    public static string $controllerNamespace = '';

    /**
     * @var array
     */
    public $bindings = [
        ScoutRoutingFactoryInterface::class => ScoutRoutingFactory::class,
        ScoutSearchParametersFactoryInterfaceAlias::class => ScoutSearchParametersFactory::class,
        FinderRoutingFactoryInterface::class => FinderRoutingFactory::class,
        FinderSearchRequestFactoryInterface::class => FinderSearchParametersFactory::class,
    ];

    /**
     * @return array<class-string, array<int, class-string|\Stancl\JobPipeline\JobPipeline>>
     */
    public function events(): array
    {
        return [
            // Tenant events
            Events\CreatingTenant::class => [],
            Events\TenantCreated::class => [
                // JobPipeline::make([
                //     Jobs\CreateDatabase::class,
                //     Jobs\MigrateDatabase::class,
                //     Jobs\SeedDatabase::class,

                //     Your own jobs to prepare the tenant.
                //     Provision API keys, create S3 buckets, anything you want!
                // ])->send(function(Events\TenantCreated $event) {
                //     return $event->tenant;
                // })->shouldBeQueued(false), // `false` by default, but you probably want to make this `true` for production.
            ],
            Events\SavingTenant::class => [],
            Events\TenantSaved::class => [],
            Events\UpdatingTenant::class => [],
            Events\TenantUpdated::class => [],
            Events\DeletingTenant::class => [],
            Events\TenantDeleted::class => [
                // JobPipeline::make([
                //     Jobs\DeleteDatabase::class,
                // ])->send(function(Events\TenantDeleted $event) {
                //     return $event->tenant;
                // })->shouldBeQueued(false), // `false` by default, but you probably want to make this `true` for production.
            ],

            // Tenancy events
            Events\InitializingTenancy::class => [],
            Events\TenancyInitialized::class => [
                Listeners\BootstrapTenancy::class,
            ],

            Events\EndingTenancy::class => [],
            Events\TenancyEnded::class => [
                Listeners\RevertToCentralContext::class,
            ],

            Events\BootstrappingTenancy::class => [],
            Events\TenancyBootstrapped::class => [],
            Events\RevertingToCentralContext::class => [],
            Events\RevertedToCentralContext::class => [],

            // Resource syncing
            Events\SyncedResourceSaved::class => [
                Listeners\UpdateSyncedResource::class,
            ],

            // Fired only when a synced resource is changed in a different DB than the origin DB (to avoid infinite loops)
            Events\SyncedResourceChangedInForeignDatabase::class => [],
        ];
    }

    public function register(): void {}

    public function boot(Dispatcher $events): void
    {
        $this->bootEvents();

        $this->makeTenancyMiddlewareHighestPriority();

        $this->addDatabaseListeners($events);

        // Fall through initialization so we can try it from the authenticated
        // user. Then we test if the tenant is allowed.
        Middleware\InitializeTenancyByRequestData::$onFail = function ($error, $request, $next) {
            return $next($request);
        };
    }

    protected function bootEvents(): void
    {
        foreach ($this->events() as $event => $listeners) {
            foreach ($listeners as $listener) {
                if ($listener instanceof JobPipeline) {
                    $listener = $listener->toListener();
                }

                Event::listen($event, $listener);
            }
        }
    }

    protected function makeTenancyMiddlewareHighestPriority(): void
    {
        $tenancyMiddleware = [
            // Even higher priority than the initialization middleware
            Middleware\PreventAccessFromCentralDomains::class,

            Middleware\InitializeTenancyByDomain::class,
            Middleware\InitializeTenancyBySubdomain::class,
            Middleware\InitializeTenancyByDomainOrSubdomain::class,
            Middleware\InitializeTenancyByPath::class,
            Middleware\InitializeTenancyByRequestData::class,
        ];

        foreach (array_reverse($tenancyMiddleware) as $middleware) {
            $this->app->make(\Illuminate\Contracts\Http\Kernel::class)->prependToMiddlewarePriority($middleware);
        }
    }

    protected function addDatabaseListeners(Dispatcher $events): void
    {
        $events->listen('eloquent.creating: *', function (string $event, array $data) {
            /** @var \Illuminate\Database\Eloquent\Model $model */
            $model = $data[0];
            if (! should_be_scoped($model)) {
                return;
            }
            $base = tenancy()->tenant;
            if ($base) {
                $model->setAttribute('base_id', $base->getKey());
            } else {
                throw new \RuntimeException('No tenant found when saving model '.$model::class);
            }
        });

        $events->listen('eloquent.booting: *', function (string $event, array $data) {
            /** @var \Illuminate\Database\Eloquent\Model $model */
            $model = $data[0];

            if (! should_be_scoped($model)) {
                return;
            }
            $model::addGlobalScope('base', function (Builder $builder) use ($model) {
                $base = tenancy()->tenant;
                if ($base) {
                    $builder->where($model->qualifyColumn('base_id'), $base->getKey());
                } elseif (! $this->app->runningInConsole()) {
                    throw new \RuntimeException('No tenant found when querying model '.$model::class);
                }
            });
        });
    }
}
