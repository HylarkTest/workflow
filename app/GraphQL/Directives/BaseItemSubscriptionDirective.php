<?php

declare(strict_types=1);

namespace App\GraphQL\Directives;

use Nuwave\Lighthouse\Support\Contracts\Directive;
use Nuwave\Lighthouse\Schema\Directives\BaseDirective;

/**
 * This directive exists as a placeholder and can be used
 * to point to a custom subscription class.
 *
 * @see \Nuwave\Lighthouse\Schema\Types\GraphQLSubscription
 */
class BaseItemSubscriptionDirective extends BaseDirective
{
    public const NAME = 'baseItemSubscription';

    public static function definition(): string
    {
        return /** @lang GraphQL */ <<<'GRAPHQL'
"""
Reference a class to handle the broadcasting of a subscription to clients.
The given class must extend `\Nuwave\Lighthouse\Schema\Types\GraphQLSubscription`.
"""
directive @baseItemSubscription(
  """
  A reference to a subclass of `\Nuwave\Lighthouse\Schema\Types\GraphQLSubscription`.
  """
  class: String!
) on FIELD_DEFINITION
GRAPHQL;
    }
}
