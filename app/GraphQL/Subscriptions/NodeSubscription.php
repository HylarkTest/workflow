<?php

declare(strict_types=1);

namespace App\GraphQL\Subscriptions;

use Nuwave\Lighthouse\Subscriptions\Subscriber;

class NodeSubscription extends BaseSubscription
{
    /**
     * Filter which subscribers should receive the subscription.
     *
     * @param  array  $root
     */
    public function filter(Subscriber $subscriber, $root): bool
    {
        if ($subscriber->socket_id && $subscriber->socket_id === request()->header('x-socket-id')) {
            return false;
        }
        /** @var \App\GraphQL\AppContext $context */
        $context = $subscriber->context;

        return $root['base'] === $context->base()->id;
    }
}
