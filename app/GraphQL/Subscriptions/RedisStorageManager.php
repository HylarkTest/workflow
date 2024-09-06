<?php

declare(strict_types=1);

namespace App\GraphQL\Subscriptions;

use Nuwave\Lighthouse\Subscriptions\Subscriber;
use Nuwave\Lighthouse\Subscriptions\Storage\RedisStorageManager as BaseRedisStorageManager;

class RedisStorageManager extends BaseRedisStorageManager
{
    public function storeSubscriber(Subscriber $subscriber, string $topic): void
    {
        // Not really sure how this works, but at some point the subscription
        // implementation serializes and caches the user model along with any
        // relationships. The `auth` route needs this cached user but doesn't
        // have multitenancy set up, so fetching the relationships fail.
        // However, the relationships are not needed to authenticate the user,
        // so we override the store method and remove any relationships.
        /** @var \App\GraphQL\AppContext $context */
        $context = $subscriber->context;
        /** @var \App\Models\User|null $user */
        $user = $context->user();
        if ($user) {
            $user->unsetRelations();
        }
        parent::storeSubscriber($subscriber, $topic);
    }
}
