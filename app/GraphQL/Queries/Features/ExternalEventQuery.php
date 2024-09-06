<?php

declare(strict_types=1);

namespace App\GraphQL\Queries\Features;

use App\Models\Item;
use App\Models\User;
use GraphQL\Deferred;
use App\GraphQL\AppContext;
use Illuminate\Support\Arr;
use LighthouseHelpers\Utils;
use Illuminate\Support\Carbon;
use App\Models\ExternalEventable;
use AccountIntegrations\Core\Scope;
use LighthouseHelpers\Core\Mutation;
use GraphQL\Type\Definition\ResolveInfo;
use App\GraphQL\ExternalAssociationBatchLoader;
use Illuminate\Database\RecordsNotFoundException;
use AccountIntegrations\Models\IntegrationAccount;
use App\Core\Mappings\Features\MappingFeatureType;
use App\GraphQL\Queries\Concerns\AddsAssociations;
use App\GraphQL\Queries\Concerns\InteractsWithIntegratedData;

class ExternalEventQuery extends Mutation
{
    use AddsAssociations;

    /**
     * @use \App\GraphQL\Queries\Concerns\InteractsWithIntegratedData<\AccountIntegrations\Core\Calendar\Event>
     */
    use InteractsWithIntegratedData {
        getSource as baseGetSource;
    }

    /**
     * @throws \Exception
     */
    public function index(?Item $rootValue, array $args, AppContext $context, ResolveInfo $resolveInfo): array
    {
        $user = $context->user();

        $node = $rootValue ?? $args['forNode'] ?? null;
        if ($node) {
            return $this->getEventsForNode($node, $args, $user);
        }

        if (! isset($args['calendarId'])) {
            $this->throwValidationException('calendarId', ['The calendarId field is required.']);
        }

        $perPage = $args['first'] ?? 100;
        $args['first'] = $perPage + 1;

        $source = $this->getSource($context, $args);

        if (isset($args['startsBefore'], $args['endsAfter'])) {
            $start = $args['endsAfter'];
            $end = $args['startsBefore'];
            unset($args['startsBefore'], $args['endsAfter']);
            $events = $source->getEventsBetween($args['calendarId'], Carbon::parse($start), Carbon::parse($end), $args);
        } else {
            $events = $source->getEvents($args['calendarId'], $args);
        }

        $count = $events->take($perPage)->count();
        $currentPage = $args['page'] ?? 1;
        $hasMorePages = $count < $events->count();

        return $this->buildExternalPaginator($events, $count, $hasMorePages, $currentPage, $perPage);
    }

    /**
     * @param  null  $rootValue
     */
    public function show($rootValue, array $args, AppContext $context, ResolveInfo $resolveInfo): \AccountIntegrations\Core\Calendar\Event
    {
        return $this->getSource($context, $args)->getEvent($args['calendarId'], $args['id']);
    }

    /**
     * @param  null  $rootValue
     *
     * @throws \Exception
     */
    public function store($rootValue, array $args, AppContext $context, ResolveInfo $resolveInfo): array
    {
        $data = Arr::only($args['input'], [
            'name',
            'startAt',
            'endAt',
            'timezone',
            'isAllDay',
            'recurrence',
            'description',
            'location',
        ]);

        $items = $this->getAssociatedItems($context->base(), $args, MappingFeatureType::EVENTS);

        $source = $this->getSource($context, $args);

        $event = $source->createEvent($args['input']['calendarId'], $data);

        foreach ($items as $item) {
            $item->externalEventables()->updateOrCreate([
                'calendar_id' => $event->calendar->baseId(),
                'event_id' => $event->mainId(),
                'integration_account_id' => $source->id,
            ]);
        }

        return $this->mutationResponse(200, 'External event was created successfully', [
            'event' => $event,
            'calendar' => $event->calendar,
        ]);
    }

    /**
     * @param  null  $rootValue
     *
     * @throws \Exception
     */
    public function update($rootValue, array $args, AppContext $context, ResolveInfo $resolveInfo): array
    {
        $data = Arr::only($args['input'], [
            'id',
            'name',
            'startAt',
            'endAt',
            'timezone',
            'isAllDay',
            'recurrence',
            'description',
            'location',
        ]);

        $source = $this->getSource($context, $args);

        $event = $source->updateEvent($args['input']['calendarId'], $data);

        return $this->mutationResponse(200, 'External event was updated successfully', [
            'event' => $event,
            'calendar' => $event->calendar,
        ]);
    }

    /**
     * @param  null  $rootValue
     *
     * @throws \Exception
     */
    public function destroy($rootValue, array $args, AppContext $context, ResolveInfo $resolveInfo): array
    {
        $source = $this->getSource($context, $args);

        $source->deleteEvent($args['input']['calendarId'], $args['input']['id']);

        ExternalEventable::query()->where([
            'integration_account_id' => $source->id,
            'event_id' => $args['input']['id'],
        ])->delete();

        return $this->mutationResponse(200, 'Event was deleted successfully');
    }

    /**
     * @param  null  $root
     *
     * @throws \Exception
     */
    public function associate($root, array $args, AppContext $context, ResolveInfo $resolveInfo): array
    {
        /** @var \App\Models\Item $node */
        $node = Utils::resolveModelFromGlobalId($args['input']['nodeId']);

        $source = $this->getSource($context, $args);

        $event = $source->getEvent($args['input']['calendarId'], $args['input']['id']);

        $node->externalEventables()->updateOrCreate([
            'calendar_id' => $event->calendar->baseId(),
            'event_id' => $event->mainId(),
            'integration_account_id' => $source->id,
        ]);

        return $this->mutationResponse(200, 'Event was associated successfully', [
            'event' => $event,
        ]);
    }

    /**
     * @param  null  $root
     *
     * @throws \Exception
     */
    public function dissociate($root, array $args, AppContext $context, ResolveInfo $resolveInfo): array
    {
        /** @var \App\Models\Item $node */
        $node = Utils::resolveModelFromGlobalId($args['input']['nodeId']);

        $source = $this->getSource($context, $args);

        $event = $source->getEvent($args['input']['calendarId'], $args['input']['id']);

        $node->externalEventables()->where([
            'calendar_id' => $event->calendar->baseId(),
            'event_id' => $event->mainId(),
            'integration_account_id' => $source->id,
        ])->delete();

        return $this->mutationResponse(200, 'Event was dissociated successfully', [
            'event' => $event,
        ]);
    }

    public function resolveAssociations(\AccountIntegrations\Core\Calendar\Event $event, array $args, AppContext $context, ResolveInfo $resolveInfo): ?Deferred
    {
        $id = $event->mainId();

        return $id ? ExternalAssociationBatchLoader::instanceFromExternal(
            Item::class, ExternalEventable::class, $event->account->id
        )->loadAndResolve($id) : null;
    }

    public function getEventsForNode(string|Item $node, array $args, User $user): array
    {
        $perPage = $args['first'] ?? 100;
        /** @var \App\Models\Item $node */
        $node = \is_string($node) ? Utils::resolveModelFromGlobalId($node) : $node;
        $externalEvents = null;
        $eventablesQuery = $node->externalEventables();
        $total = $eventablesQuery->count();
        $page = $args['page'] ?? 1;

        $fetchExternalEvents = function () use ($user, $eventablesQuery, $perPage, &$page, &$externalEvents) {
            if ($externalEvents !== null) {
                $count = $externalEvents->take($perPage)->count();
                $hasMorePages = $count < $externalEvents->count();

                return [$count, $hasMorePages, $externalEvents->take($perPage)];
            }
            $externalEvents = collect([]);
            do {
                $events = $eventablesQuery->offset(($page - 1) * $perPage)->limit($perPage + 1)
                    ->get()
                    ->map(function (ExternalEventable $eventable) use ($user) {
                        /** @var \AccountIntegrations\Models\IntegrationAccount|null $source */
                        $source = $user->integrationAccounts->find($eventable->integration_account_id);
                        try {
                            $event = $source?->getEvent($eventable->calendar_id, $eventable->event_id);
                        } catch (RecordsNotFoundException) {
                            $eventable->delete();

                            return null;
                        }

                        return $event;
                    })->filter();
                $externalEvents = $externalEvents->merge($events);
                $page++;
            } while ($externalEvents->count() <= $perPage && $events->isNotEmpty());

            $count = $externalEvents->take($perPage)->count();
            $hasMorePages = $count < $externalEvents->count();

            return [$count, $hasMorePages, $externalEvents->take($perPage)];
        };

        return $this->buildExternalPaginator(
            fn () => $fetchExternalEvents()[2],
            fn () => $fetchExternalEvents()[0],
            fn () => $fetchExternalEvents()[1],
            $page,
            $perPage,
            $total
        );
    }

    protected function getSource(AppContext $context, array $args): IntegrationAccount
    {
        return $this->baseGetSource($context, $args, Scope::CALENDAR);
    }
}
