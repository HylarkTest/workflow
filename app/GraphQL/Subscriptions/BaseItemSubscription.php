<?php

declare(strict_types=1);

namespace App\GraphQL\Subscriptions;

use App\Models\Base;
use Illuminate\Http\Request;
use Nuwave\Lighthouse\Subscriptions\Subscriber;
use Nuwave\Lighthouse\Schema\Types\GraphQLSubscription;

class BaseItemSubscription extends GraphQLSubscription
{
    /**
     * Check if subscriber is allowed to listen to the subscription.
     */
    public function authorize(Subscriber $subscriber, Request $request): bool
    {
        return (bool) $subscriber->context->user();
    }

    /**
     * Filter which subscribers should receive the subscription.
     *
     * @param  array  $root
     */
    public function filter(Subscriber $subscriber, $root): bool
    {
        if ($subscriber->socket_id
                && ! ($root['toAll'] ?? false)
                && $subscriber->socket_id === request()->header('x-socket-id')
        ) {
            return false;
        }
        /** @var \App\GraphQL\AppContext $context */
        $context = $subscriber->context;

        $key = $root['base'] instanceof Base ? $root['base']->id : $root['base'];

        return $key === $context->base()->id;
    }
}
