<?php

declare(strict_types=1);

namespace App\GraphQL\Subscriptions;

use Nuwave\Lighthouse\Schema\Types\GraphQLSubscription;
use Nuwave\Lighthouse\Subscriptions\SubscriptionBroadcaster as BaseSubscriptionBroadcaster;

/**
 * Because of the conventions we use for mutation responses, and using the same
 * structure for subscriptions, Laravel cannot by default efficiently serialize
 * the models in the response as they are nested in an array.
 * We need to override the broadcaster to dispatch a custom job that knows how
 * to serialize the response.
 */
class SubscriptionBroadcaster extends BaseSubscriptionBroadcaster
{
    /**
     * @param  mixed  $root
     */
    public function queueBroadcast(GraphQLSubscription $subscription, string $fieldName, $root): void
    {
        $broadcastSubscriptionJob = new BroadcastSubscriptionJob($subscription, $fieldName, $root);
        $broadcastSubscriptionJob->onQueue(config('lighthouse.subscriptions.broadcasts_queue_name'));

        $this->busDispatcher->dispatch($broadcastSubscriptionJob);
    }
}
