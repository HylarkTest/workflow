<?php

declare(strict_types=1);

namespace App\GraphQL\Subscriptions;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Nuwave\Lighthouse\Subscriptions\Authorizer as BaseAuthorizer;

class Authorizer extends BaseAuthorizer
{
    public function authorize(Request $request): bool
    {
        try {
            $channel = $request->input('channel_name');
            if (! \is_string($channel)) {
                return false;
            }

            $channel = $this->sanitizeChannelName($channel);

            $subscriber = $this->storage->subscriberByChannel($channel);
            if ($subscriber === null) {
                return false;
            }

            // Lighthouse expects all subscriptions to be on the root subscription
            // query. But we want to namespace subscriptions for the dynamic API,
            // so they don't clash. If the subscription is for one of the dynamic
            // API endpoints then we can manually set the subscription.
            if (Str::startsWith($subscriber->topic, 'ITEMS.')) {
                $subscriptions = collect([resolve(BaseItemSubscription::class)]);
            } else {
                $subscriptions = $this->registry->subscriptions($subscriber);
            }
            if ($subscriptions->isEmpty()) {
                return false;
            }

            foreach ($subscriptions as $subscription) {
                if (! $subscription->authorize($subscriber, $request)) {
                    $this->storage->deleteSubscriber($subscriber->channel);

                    return false;
                }
            }

            return true;
        } catch (\Exception $exception) {
            $this->exceptionHandler->handleAuthError($exception);

            return false;
        }
    }
}
