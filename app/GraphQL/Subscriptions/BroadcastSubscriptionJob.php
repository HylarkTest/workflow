<?php

declare(strict_types=1);

namespace App\GraphQL\Subscriptions;

use Nuwave\Lighthouse\Schema\Types\GraphQLSubscription;
use Nuwave\Lighthouse\Subscriptions\Contracts\BroadcastsSubscriptions;
use Nuwave\Lighthouse\Subscriptions\BroadcastSubscriptionJob as BaseJob;

/**
 * Because of the conventions we use for mutation responses, and using the same
 * structure for subscriptions, Laravel cannot by default efficiently serialize
 * the models in the response as they are nested in an array.
 * This job checks if the $root attribute is an array and loops through it to
 * serialize the models inside.
 */
class BroadcastSubscriptionJob extends BaseJob
{
    protected ?string $socketId;

    public function __construct(GraphQLSubscription $subscription, string $fieldName, mixed $root)
    {
        parent::__construct($subscription, $fieldName, $root);
        $this->socketId = request()->header('x-socket-id');
    }

    public function handle(BroadcastsSubscriptions $broadcaster): void
    {
        request()->headers->set('x-socket-id', $this->socketId);
        parent::handle($broadcaster);
    }

    protected function getSerializedPropertyValue($value, $withRelations = true)
    {
        if (\is_array($value)) {
            foreach ($value as $key => $item) {
                $value[$key] = $this->getSerializedPropertyValue($item, $withRelations);
            }

            return $value;
        }

        return parent::getSerializedPropertyValue($value, $withRelations);
    }

    protected function getRestoredPropertyValue($value)
    {
        if (\is_array($value)) {
            foreach ($value as $key => $item) {
                $value[$key] = $this->getRestoredPropertyValue($item);
            }

            return $value;
        }

        return parent::getRestoredPropertyValue($value);
    }
}
