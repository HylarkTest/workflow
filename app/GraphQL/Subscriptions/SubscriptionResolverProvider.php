<?php

declare(strict_types=1);

namespace App\GraphQL\Subscriptions;

use Illuminate\Support\Str;
use LighthouseHelpers\Utils;
use Illuminate\Container\Container;
use Nuwave\Lighthouse\Schema\AST\ASTHelper;
use Nuwave\Lighthouse\Execution\ResolveInfo;
use Nuwave\Lighthouse\Schema\Values\FieldValue;
use Nuwave\Lighthouse\Subscriptions\Subscriber;
use Nuwave\Lighthouse\Schema\Types\GraphQLSubscription;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use Nuwave\Lighthouse\Subscriptions\Directives\SubscriptionDirective;
use Nuwave\Lighthouse\Subscriptions\Exceptions\UnauthorizedSubscriber;
use Nuwave\Lighthouse\Subscriptions\SubscriptionResolverProvider as BaseSubscriptionResolverProvider;

/**
 * Most of our subscriptions don't need any special behaviour and the logic for
 * authenticating and filtering them is the same.
 * Here we override the default provider to include a default Subscription class.
 */
class SubscriptionResolverProvider extends BaseSubscriptionResolverProvider
{
    public function provideSubscriptionResolver(FieldValue $fieldValue): \Closure
    {
        $fieldName = $fieldValue->getFieldName();

        $directive = ASTHelper::directiveDefinition($fieldValue->getField(), SubscriptionDirective::NAME);
        $className = $directive === null
            ? Str::studly($fieldName)
            : ASTHelper::directiveArgValue($directive, 'class');

        $namespacesToTry = $fieldValue->parentNamespaces();
        $namespacedClassName = Utils::namespaceClassname(
            $className,
            $namespacesToTry,
            static fn (string $class): bool => is_subclass_of($class, GraphQLSubscription::class),
        );

        if ($namespacedClassName === null) {
            $namespacedClassName = BaseItemSubscription::class;
        }

        \assert(is_subclass_of($namespacedClassName, GraphQLSubscription::class));

        $subscription = Container::getInstance()->make($namespacedClassName);
        // Subscriptions can only be placed on a single field on the root
        // query, so there is no need to consider the field path
        $this->subscriptionRegistry->register($subscription, $fieldName);

        return function (mixed $root, array $args, GraphQLContext $context, ResolveInfo $resolveInfo) use ($subscription, $fieldName) {
            if ($root instanceof Subscriber) {
                return $subscription->resolve($root->root, $args, $context, $resolveInfo);
            }

            $subscriber = new Subscriber($args, $context, $resolveInfo);

            if (! $subscription->can($subscriber)) {
                throw new UnauthorizedSubscriber('Unauthorized subscription request');
            }

            $this->subscriptionRegistry->subscriber(
                $subscriber,
                $subscription->encodeTopic($subscriber, $fieldName),
            );

            return null;
        };
    }
}
