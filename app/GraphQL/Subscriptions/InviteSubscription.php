<?php

declare(strict_types=1);

namespace App\GraphQL\Subscriptions;

use Illuminate\Http\Request;
use Nuwave\Lighthouse\Subscriptions\Subscriber;
use Nuwave\Lighthouse\Schema\Types\GraphQLSubscription;

class InviteSubscription extends GraphQLSubscription
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
     * @param  \App\Models\MemberInvite  $root
     */
    public function filter(Subscriber $subscriber, $root): bool
    {
        if ($subscriber->socket_id && $subscriber->socket_id === request()->header('x-socket-id')) {
            return false;
        }
        /** @var \App\GraphQL\AppContext $context */
        $context = $subscriber->context;

        return $root->base->id === $context->base()->id;
    }
}
