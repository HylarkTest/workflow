<?php

declare(strict_types=1);

namespace App\GraphQL\Subscriptions;

use Illuminate\Http\Request;
use Nuwave\Lighthouse\Subscriptions\Subscriber;
use Nuwave\Lighthouse\Schema\Types\GraphQLSubscription;

class AccountIntegrated extends GraphQLSubscription
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
     * @param  \AccountIntegrations\Models\IntegrationAccount  $root
     */
    public function filter(Subscriber $subscriber, $root): bool
    {
        /** @var \App\Models\User $user */
        $user = $subscriber->context->user();

        return $root->accountOwner->is($user);
    }
}
