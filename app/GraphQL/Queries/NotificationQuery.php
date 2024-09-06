<?php

declare(strict_types=1);

namespace App\GraphQL\Queries;

use Lampager\Paginator;
use App\GraphQL\AppContext;
use App\Models\DatabaseNotification;
use LighthouseHelpers\Core\Mutation;
use GraphQL\Type\Definition\ResolveInfo;
use App\Core\Preferences\UserPreferences;
use App\Core\Preferences\NotificationChannel;
use GraphQL\Executor\Promise\Adapter\SyncPromise;
use App\GraphQL\Queries\Concerns\PaginatesQueries;

class NotificationQuery extends Mutation
{
    use PaginatesQueries;

    /**
     * @param  null  $rootValue
     * @param  array{channel?: string, filter?: string, first: int, after?: string}  $args
     */
    public function index($rootValue, array $args, AppContext $context): SyncPromise
    {
        $user = $context->user();
        // Not using citus as the users table is not distributed.
        $query = DatabaseNotification::query()
            ->where([
                'notifiable_type' => $user->getMorphClass(),
                'notifiable_id' => $user->getKey(),
            ]);

        $preFilterQuery = (clone $query);

        if (isset($args['channel'])) {
            match ($args['channel']) {
                'ALL' => null,
                default => $query->filterChannel($args['channel']),
            };
        }

        if (isset($args['filter'])) {
            match ($args['filter']) {
                'ONLY_CLEARED' => $query->read(),
                'ONLY_UNCLEARED' => $query->unread(),
                default => null,
            };
        }

        $promise = $this->paginateQuery($query, $args, function (Paginator $lampager) {
            $lampager->orderByDesc('created_at')
                ->orderByDesc('id');
        });

        return $promise->then(function ($paginator) use ($preFilterQuery, $user) {
            $paginator->meta = [
                'clearedCount' => static fn () => (clone $preFilterQuery)->read()->count(),
                'unclearedCount' => static fn () => (clone $preFilterQuery)->unread()->count(),
                'newCount' => static fn () => $user->newNotifications()->count(),
                'channels' => array_map(function (NotificationChannel $channel) use ($preFilterQuery): array {
                    $channelFilterQuery = (clone $preFilterQuery)->filterChannel($channel->value);

                    return [
                        'channel' => $channel->value,
                        'clearedCount' => static fn () => (clone $channelFilterQuery)->read()->count(),
                        'unclearedCount' => static fn () => (clone $channelFilterQuery)->unread()->count(),
                    ];
                }, NotificationChannel::cases()),
            ];

            return $paginator;
        });
    }

    /**
     * @param  null  $rootValue
     * @param  array{input: array{id: string}}  $args
     */
    public function clear($rootValue, array $args, AppContext $context, ResolveInfo $resolveInfo): array
    {
        $id = $args['input']['id'];

        $user = $context->user();
        /** @var \App\Models\DatabaseNotification $notification */
        $notification = $user->notifications()->findOrFail($id);

        $notification->markAsRead();

        return $this->mutationResponse(200, 'Notification was updated successfully', [
            'notification' => $notification,
        ]);
    }

    /**
     * @param  null  $rootValue
     * @param  array{input: array{id: string}}  $args
     */
    public function unclear($rootValue, array $args, AppContext $context, ResolveInfo $resolveInfo): array
    {
        $id = $args['input']['id'];

        $user = $context->user();
        /** @var \App\Models\DatabaseNotification $notification */
        $notification = $user->notifications()->findOrFail($id);

        $notification->markAsUnread();

        return $this->mutationResponse(200, 'Notification was updated successfully', [
            'notification' => $notification,
        ]);
    }

    /**
     * @param  null  $rootValue
     */
    public function clearAll($rootValue, array $args, AppContext $context, ResolveInfo $resolveInfo): array
    {
        $user = $context->user();

        $user->unreadNotifications()->update(['read_at' => now()]);

        return $this->mutationResponse(201, 'Notifications were updated successfully');
    }

    /**
     * @param  null  $rootValue
     */
    public function markAsSeen($rootValue, array $args, AppContext $context, ResolveInfo $resolveInfo): array
    {
        $user = $context->user();

        $user->settings->updatePreferences(function (UserPreferences $preferences) {
            $preferences->lastSeenNotifications = now();
        });

        return $this->mutationResponse(201, 'Notifications were marked as seen', [
            'user' => $user,
        ]);
    }
}
