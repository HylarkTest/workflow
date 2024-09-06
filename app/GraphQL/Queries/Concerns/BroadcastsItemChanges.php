<?php

declare(strict_types=1);

namespace App\GraphQL\Queries\Concerns;

use App\Models\Item;
use App\GraphQL\Subscriptions\BaseItemSubscription;
use Nuwave\Lighthouse\Execution\Utils\Subscription;
use Nuwave\Lighthouse\Subscriptions\Contracts\BroadcastsSubscriptions;

trait BroadcastsItemChanges
{
    protected function broadcastResponse(string $fieldName, array $response, bool $shouldQueue = true): void
    {
        $broadcaster = resolve(BroadcastsSubscriptions::class);
        $subscription = resolve(BaseItemSubscription::class);
        if ($shouldQueue) {
            $broadcaster->queueBroadcast($subscription, $fieldName, $response);
        } else {
            $broadcaster->broadcast($subscription, $fieldName, $response);
        }
    }

    protected function broadcastItemChange(Item $item): void
    {
        $mapping = $item->mapping;
        $this->broadcastResponse(
            "items.$mapping->graphql_many_field.{$mapping->graphql_single_field}Updated",
            $this->itemMutationResponse($item)
        );

        Subscription::broadcast(
            'itemUpdated',
            $this->mutationResponse(200, 'The item was updated successfully', ['item' => $item])
        );
        Subscription::broadcast(
            'nodeUpdated',
            $this->mutationResponse(200, 'The item was updated successfully', ['node' => $item, 'event' => 'itemUpdated'])
        );
    }

    protected function broadcastItemDeleted(Item $item): void
    {
        $mapping = $item->mapping;

        $this->broadcastResponse(
            "items.$mapping->graphql_many_field.{$mapping->graphql_single_field}Deleted",
            $this->itemMutationResponse($item, 204, 'deleted'),
            false
        );

        Subscription::broadcast(
            'itemDeleted',
            $this->mutationResponse(204, 'The item was deleted successfully', ['item' => $item]),
            false
        );
        Subscription::broadcast(
            'nodeDeleted',
            $this->mutationResponse(200, 'The item was updated successfully', ['event' => 'itemDeleted'])
        );
    }

    protected function itemMutationResponse(Item $item, int $code = 200, string $action = 'updated'): array
    {
        $mapping = $item->mapping;

        return $this->mutationResponse(
            $code,
            "The $mapping->graphql_single_field was $action successfully",
            [
                $mapping->graphql_single_field => $item,
            ]
        );
    }
}
