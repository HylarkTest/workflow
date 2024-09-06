<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\GraphQL\Subscriptions\Authorizer;
use Illuminate\Support\Facades\Broadcast;
use App\GraphQL\Subscriptions\RedisStorageManager;
use App\GraphQL\Subscriptions\SubscriptionBroadcaster;
use App\GraphQL\Subscriptions\SubscriptionResolverProvider;
use Nuwave\Lighthouse\Support\Contracts\ProvidesSubscriptionResolver;
use Nuwave\Lighthouse\Subscriptions\Contracts\AuthorizesSubscriptions;
use Nuwave\Lighthouse\Subscriptions\Contracts\BroadcastsSubscriptions;
use Nuwave\Lighthouse\Subscriptions\Storage\RedisStorageManager as BaseRedisStorageManager;

class BroadcastServiceProvider extends ServiceProvider
{
    public array $bindings = [
        BaseRedisStorageManager::class => RedisStorageManager::class,
        ProvidesSubscriptionResolver::class => SubscriptionResolverProvider::class,
        BroadcastsSubscriptions::class => SubscriptionBroadcaster::class,
        AuthorizesSubscriptions::class => Authorizer::class,
    ];

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Broadcast::routes();

        require base_path('routes/channels.php');
    }
}
