<?php

declare(strict_types=1);

namespace App\GraphQL\Queries\Features;

use App\Models\Base;
use App\GraphQL\AppContext;
use GraphQL\Type\Definition\ResolveInfo;
use App\Models\Contracts\FeatureListItem;
use App\Core\Features\Repositories\EventItemRepository;

/**
 * @extends \App\GraphQL\Queries\Features\FeatureListItemQuery<\App\Models\Event, \App\Models\Calendar>
 */
class EventQuery extends FeatureListItemQuery
{
    public static string $itemQueryParams = <<<'GRAPHQL'
        startsBefore: DateTime
        startsAfter: DateTime
        endsBefore: DateTime
        endsAfter: DateTime
        includeRecurringInstances: Boolean
    GRAPHQL;

    public function show($rootValue, array $args, AppContext $context, ResolveInfo $resolveInfo): FeatureListItem
    {
        $id = $args['id'];
        if ($args['full'] ?? true) {
            $id = strtok($id, '_');
        }

        return parent::show($rootValue, ['id' => $id], $context, $resolveInfo);
    }

    protected function filterArgKeys(): array
    {
        return [
            ...parent::filterArgKeys(),
            'endsAfter',
            'endsBefore',
            'startsAfter',
            'startsBefore',
            'maxPriority',
            'minPriority',
            'search',
            'includeRecurringInstances',
        ];
    }

    protected function getUpdateDataKeys(): array
    {
        return [
            ...$this->getCreateDataKeys(),
            'thisAndFuture',
        ];
    }

    protected function getCreateDataKeys(): array
    {
        return [
            'name',
            'startAt',
            'endAt',
            'timezone',
            'isAllDay',
            'recurrence',
            'description',
            'location',
            'priority',
        ];
    }

    protected function repository(): EventItemRepository
    {
        return resolve(EventItemRepository::class);
    }

    protected function getListKey(): string
    {
        return 'calendar';
    }

    protected function getItemKey(): string
    {
        return 'event';
    }

    protected function validateData(Base $base, array $data): void
    {
        if (! $base->accountLimits()->canCreateEvents()) {
            $this->throwValidationException('limit', trans('validation.exceeded'));
        }
    }
}
