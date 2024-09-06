<?php

declare(strict_types=1);

namespace App\GraphQL\Subscriptions;

use Illuminate\Http\Request;
use LighthouseHelpers\Utils;
use App\Models\Contracts\ProgressTask;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Subscriptions\Subscriber;
use Nuwave\Lighthouse\Schema\Types\GraphQLSubscription;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class ProgressTrackerUpdatedSubscription extends GraphQLSubscription
{
    /**
     * Check if subscriber is allowed to listen to the subscription.
     */
    public function authorize(Subscriber $subscriber, Request $request): bool
    {
        $baseId = $request->header('X-Base-Id');
        if (! $baseId) {
            return false;
        }
        /** @var \App\Models\Base $baseModel */
        $baseModel = Utils::resolveModelFromGlobalId($baseId);
        tenancy()->initialize($baseModel);
        $id = $subscriber->args['taskId'];
        $progressModel = Utils::resolveModelFromGlobalId($id);
        if (! $progressModel instanceof ProgressTask) {
            return false;
        }

        return (bool) $subscriber->context->user()?->can('access', $progressModel->getRelationValue('base'));
    }

    /**
     * Filter which subscribers should receive the subscription.
     *
     * @param  \App\Events\Core\ProgressTrackerUpdated  $root
     */
    public function filter(Subscriber $subscriber, $root): bool
    {
        $id = $subscriber->args['taskId'];
        $model = Utils::resolveModelFromGlobalId($id);
        if (! $model instanceof ProgressTask) {
            return false;
        }

        return $root->taskId === $model->taskId();
    }

    /**
     * @param  \App\Events\Core\ProgressTrackerUpdated  $root
     * @return array{
     *     id: string,
     *     status: string,
     *     progress: ?float,
     *     message: string,
     *     estimatedTimeRemaining: int|null,
     * }
     */
    public function resolve($root, array $args, GraphQLContext $context, ResolveInfo $resolveInfo): mixed
    {
        return [
            'id' => $root->taskId,
            'status' => $root->status->value,
            'progress' => $root->progress,
            'message' => $root->message,
            'estimatedTimeRemaining' => $root->estimatedTimeRemaining,
            'startedAt' => $root->startedAt,
            'finishedAt' => $root->finishedAt,
            'processedCount' => $root->processedCount,
            'totalCount' => $root->totalCount,
        ];
    }
}
