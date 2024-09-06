<?php

declare(strict_types=1);

namespace App\GraphQL\Queries;

use App\GraphQL\AppContext;
use Timekeeper\Core\DeadlineStatus;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Contracts\Database\Query\Builder;

class TimekeeperQuery
{
    /**
     * @param  null  $rootValue
     *
     * @throws \JsonException
     */
    public function stats($rootValue, array $args, AppContext $context, ResolveInfo $resolveInfo): array
    {
        $base = $context->base();

        $query = $base->items();

        if (isset($args['forMapping'])) {
            $mapping = $base->mappings()->findOrFail($args['forMapping']);
            $query->where('mapping_id', $mapping->id);
        }

        return [
            'open' => fn () => (clone $query)->where(function (Builder $query) {
                $query->whereNotNull('start_at')
                    ->orWhereNotNull('due_by');
            })->whereNull('completed_at')->count(),
            'active' => fn () => DeadlineStatus::ACTIVE->scopeQuery(clone $query)->count(),
            'waitingToStart' => fn () => DeadlineStatus::WAITING_TO_START->scopeQuery(clone $query)->count(),
            'overdue' => fn () => DeadlineStatus::OVERDUE->scopeQuery(clone $query)->count(),
            'completed' => fn () => DeadlineStatus::COMPLETED->scopeQuery(clone $query)->count(),
        ];
    }
}
