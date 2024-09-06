<?php

declare(strict_types=1);

namespace App\GraphQL\Directives;

use Nuwave\Lighthouse\Schema\Values\FieldValue;
use Nuwave\Lighthouse\Execution\Utils\Subscription;
use Nuwave\Lighthouse\Subscriptions\Directives\BroadcastDirective;

abstract class BroadcastNodeEventDirective extends BroadcastDirective
{
    abstract protected static function getNodeEvent(): string;

    public static function definition(): string
    {
        $nodeEvent = static::getNodeEvent();
        $directiveName = 'broadcast'.ucfirst($nodeEvent);

        return /** @lang GraphQL */ <<<GRAPHQL
"""
Broadcast the results of a mutation to subscribed clients.

Ensure you place this after other field middleware directives that may transform the
result to broadcast the final value.
"""
directive @$directiveName(
  """
  Name of the specific subscription that should be broadcast as a result of this operation.
  """
  subscription: String!

  """
  Name of the field in the payload that represents the node.
  """
  nodeField: String

  """
  Specify whether or not the job should be queued.
  This defaults to the global config option `lighthouse.subscriptions.queue_broadcasts`.
  """
  shouldQueue: Boolean
) repeatable on FIELD_DEFINITION
GRAPHQL;
    }

    public function handleField(FieldValue $fieldValue): void
    {
        $subscriptionField = $this->directiveArgValue('subscription');
        $nodeField = $this->directiveArgValue('nodeField');
        $shouldQueue = $this->directiveArgValue('shouldQueue');

        $fieldValue->resultHandler(static function ($root) use ($subscriptionField, $nodeField, $shouldQueue) {
            Subscription::broadcast($subscriptionField, $root, $shouldQueue);
            $nodeEvent = static::getNodeEvent();
            Subscription::broadcast($nodeEvent, [
                ...$root,
                'node' => $nodeField ? ($root[$nodeField] ?? null) : null,
                'event' => $subscriptionField,
            ], false);

            return $root;
        });
    }
}
