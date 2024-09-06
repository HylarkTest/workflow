<?php

declare(strict_types=1);

namespace App\Core\Features\Repositories;

use App\Models\Base;
use App\Models\Item;
use App\Models\Event;
use App\Models\Space;
use App\Models\Mapping;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use LighthouseHelpers\Pagination\Cursor;
use Nuwave\Lighthouse\GlobalId\GlobalId;
use App\Models\Contracts\FeatureListItem;
use Illuminate\Database\Eloquent\Builder;
use GraphQL\Executor\Promise\Adapter\SyncPromise;
use App\Core\Mappings\Features\MappingFeatureType;
use LighthouseHelpers\Pagination\PaginationResult;
use LighthouseHelpers\Exceptions\ValidationException;

/**
 * @extends FeatureItemRepository<\App\Models\Event, \App\Models\Calendar>
 *
 * @method \Illuminate\Database\Eloquent\Builder<\App\Models\Event> getRawBuilder(Base $base, ?array $listIds = [], Item|string|null $node = null, int|Mapping|null $mapping = null, Space|int|null $space = null)
 */
class EventItemRepository extends FeatureItemRepository
{
    /**
     * @param  PaginationArgs  $paginationArgs
     * @param  array<int, int>  $listIds
     * @param array{
     *     search?: string,
     *     filter?: string,
     *     markers?: string[],
     *     includeRecurringInstances?: bool,
     *     startsBefore?: string,
     *     endsAfter?: string,
     *     maxPriority?: int,
     *     minPriority?: int,
     * } $filters
     * @param  OrderBy  $orderBy
     */
    public function paginateFeatureItems(
        Base $base,
        array $paginationArgs,
        ?array $listIds = [],
        Item|string|null $node = null,
        Mapping|int|null $mapping = null,
        Space|int|null $space = null,
        array $filters = [],
        array $orderBy = [],
        ?string $group = null,
    ): array|SyncPromise {
        if ($filters['includeRecurringInstances'] ?? false) {
            $validationMessages = [];
            if ($group) {
                $validationMessages = array_merge($validationMessages, [
                    'group' => 'Cannot group instance events',
                ]);
            }
            // As recurring events are expanded, we need to ensure that we have a start and end date, or ensure we only
            // need to check one event.
            if (! isset($filters['endsAfter']) && (! isset($filters['startsBefore']) || $paginationArgs['first'] !== 1)) {
                $validationMessages = array_merge($validationMessages, [
                    'includeRecurringInstances' => [trans('validation.includeRecurringInstances')],
                ]);
            }

            if ($validationMessages) {
                throw ValidationException::withMessages($validationMessages);
            }

            $query = $this->getRawBuilder($base, $listIds, $node, $mapping, $space);

            $this->applyFiltersToQuery($query, $filters);

            /** @var \Planner\Models\EventCollection<int> $events */
            $events = $query->orderBy('start_at')->orderBy('id')->get();
            // We validate above that if `startsBefore` is not set then `first` must be 1.
            /** @phpstan-ignore-next-line We ensured this was true in the previous check */
            $expandedEvents = $events->expandRecurringEvents($filters['endsAfter'], $filters['startsBefore'] ?? null);
            /** @phpstan-ignore-next-line Not sure the best way to resolve this */
            $expandedEvents->each(fn (Event $event) => $event->setAttribute('cursor', ['id' => $event->instanceId()]));

            $index = 0;

            $total = $expandedEvents->count();

            if (isset($paginationArgs['after'])) {
                // There should only be an id in the cursor object
                $cursor = Cursor::decode($paginationArgs)['id'];
                /** @phpstan-ignore-next-line Not sure the best way to resolve this */
                $index = $expandedEvents->search(fn (Event $event) => $event->instanceId() === $cursor);
                if ($index !== false) {
                    $index++;
                } else {
                    $index = $expandedEvents->count();
                }
            }

            $eventsPage = $expandedEvents->slice($index, $paginationArgs['first']);
            $hasPrevious = $total && $index > $total;
            $hasNext = $expandedEvents->has($index + $paginationArgs['first'] + 1);
            $previousCursor = $hasPrevious ? $eventsPage->first()?->getAttribute('cursor') : null;
            $nextCursor = $hasNext ? $eventsPage->last()?->getAttribute('cursor') : null;

            $meta = compact('hasNext', 'hasPrevious', 'nextCursor', 'previousCursor');

            return new SyncPromise(fn () => new PaginationResult($eventsPage, $meta, $expandedEvents->count()));
        }

        return parent::paginateFeatureItems($base, $paginationArgs, $listIds, $node, $mapping, $space, $filters, $orderBy, $group);
    }

    public function getFeatureItem(Base $base, string|int $id, bool $withTrashed = false): Event
    {
        [$id, $instanceId] = $this->splitId($id);

        $item = parent::getFeatureItem($base, $id, $withTrashed);
        if ($instanceId) {
            $item = $item->getInstance($instanceId);
        }

        return $item;
    }

    public function deleteFeatureItem(Base $base, int|string $id, array $args = []): bool
    {
        $force = $args['force'] ?? false;
        [$id, $instanceId] = $this->splitId($id);
        $event = $this->getFeatureItem($base, $id, $force);

        if ($instanceId) {
            return $event->deleteInstance($instanceId, $args['thisAndFuture'] ?? false, $force);
        }

        if ($force) {
            return (bool) $event->forceDelete();
        }

        return (bool) $event->delete();
    }

    public function updateFeatureItem(Base $base, int|string $id, array $data): FeatureListItem
    {
        [$id, $instanceId] = $this->splitId($id);

        return parent::updateFeatureItem($base, $id, [
            ...$data,
            'instanceId' => $instanceId,
        ]);
    }

    /**
     * @return array{0: int, 1: string|null}
     */
    protected function splitId(string|int $id): array
    {
        $instanceId = null;
        if (\is_string($id)) {
            $isInstanceId = preg_match('/([a-zA-Z0-9=]+)_(\d{8}T\d{6}Z)/', $id, $matches);
            $id = $isInstanceId ? $matches[1] : $id;
            $instanceId = $isInstanceId ? $matches[2] : null;
        }
        if (! is_numeric($id)) {
            $id = resolve(GlobalId::class)->decodeID($id);
        }

        return [(int) $id, $instanceId];
    }

    protected function getListOrderByField(): string
    {
        return 'calendar';
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<\App\Models\Event>  $query
     *
     * @throws \Nuwave\Lighthouse\Exceptions\ValidationException
     */
    protected function applyFiltersToQuery(Builder $query, array $filters, string $bool = 'AND'): void
    {
        $includeRecurringInstances = $filters['includeRecurringInstances'] ?? false;

        if ($includeRecurringInstances) {
            // If we are fetching the recurring instances then we need to filter
            // all events that start before the end of the range and end after
            // the beginning of the range, as well as fetching all recurring
            // events that start before the start of the range and end after the
            // start of the range.
            $query->inRange($filters['endsAfter'], $filters['startsBefore'] ?? null, true);
        } else {
            if (isset($filters['startsBefore'])) {
                $query->startsBefore($filters['startsBefore']);
            }

            if (isset($filters['endsAfter'])) {
                $query->endsAfter($filters['endsAfter']);
            }
        }

        if (isset($filters['maxPriority'])) {
            $query->where('priority', '<=', $filters['maxPriority'])
                ->where('priority', '<>', 0);
        }

        if (isset($filters['minPriority'])) {
            $query->where('priority', '>=', $filters['minPriority'])
                ->where('priority', '<>', 0);
        }

        parent::applyFiltersToQuery($query, $filters, $bool);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder<\App\Models\Event>
     */
    protected function getItemQuery(Base $base): Builder
    {
        return $base->events()->getQuery();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder<\App\Models\Calendar>
     */
    protected function getListQuery(Base $base): Builder
    {
        return $base->calendars()->getQuery();
    }

    /**
     * @param  \App\Models\Event  $item
     */
    protected function updateFeatureItemFromAttributes(FeatureListItem $item, array $data): Event
    {
        $thisAndFuture = $data['thisAndFuture'] ?? false;
        $instanceId = $data['instanceId'] ?? null;
        $data = Arr::except($data, ['thisAndFuture', 'instanceId']);
        if ($instanceId) {
            // The Events package doesn't check for camel case keys.
            $snakeCaseData = collect($data)->mapWithKeys(fn ($value, $key) => [Str::snake($key) => $value]);
            if ($snakeCaseData->isNotEmpty()) {
                $item = $item->updateInstance($instanceId, $snakeCaseData->all(), $thisAndFuture);
            }
        } else {
            $item->forceFill($data);
        }

        return $item;
    }

    protected function getFeatureType(): MappingFeatureType
    {
        return MappingFeatureType::EVENTS;
    }
}
