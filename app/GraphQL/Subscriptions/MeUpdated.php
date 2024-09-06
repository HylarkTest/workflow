<?php

declare(strict_types=1);

namespace App\GraphQL\Subscriptions;

use App\Models\User;
use Illuminate\Http\Request;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Subscriptions\Subscriber;
use Nuwave\Lighthouse\Schema\Types\GraphQLSubscription;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class MeUpdated extends GraphQLSubscription
{
    /**
     * Check if subscriber is allowed to listen to the subscription.
     */
    public function authorize(Subscriber $subscriber, Request $request): bool
    {
        return true;
    }

    /**
     * Filter which subscribers should receive the subscription.
     *
     * @param  \App\Models\User|array{user: \App\Models\User}  $root
     */
    public function filter(Subscriber $subscriber, $root): bool
    {
        /** @var \App\Models\User $user */
        $user = $subscriber->context->user();

        $me = \is_array($root) ? $root['user'] : $root;

        return $me->is($user);
    }

    /**
     * @param  \App\Models\User|array{user: \App\Models\User}  $root
     * @return array
     */
    public function resolve($root, array $args, GraphQLContext $context, ResolveInfo $resolveInfo): mixed
    {
        return \is_array($root) ? $root : [
            'success' => true,
            'code' => 200,
            'message' => 'User updated',
            'user' => $root,
        ];
    }
}
