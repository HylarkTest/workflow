<?php

declare(strict_types=1);

namespace App\GraphQL\Queries\Features;

use Color\Color;
use App\GraphQL\AppContext;
use Illuminate\Support\Arr;
use AccountIntegrations\Core\Scope;
use LighthouseHelpers\Core\Mutation;
use GraphQL\Type\Definition\ResolveInfo;
use AccountIntegrations\Models\IntegrationAccount;
use Nuwave\Lighthouse\Exceptions\ValidationException;
use App\GraphQL\Queries\Concerns\InteractsWithIntegratedData;
use AccountIntegrations\Core\Calendar\Calendar as IntegrationCalendar;

class ExternalCalendarQuery extends Mutation
{
    /**
     * @use \App\GraphQL\Queries\Concerns\InteractsWithIntegratedData<\Planner\Models\Calendar>
     */
    use InteractsWithIntegratedData {
        getSource as baseGetSource;
    }

    /**
     * @param  null  $rootValue
     *
     * @throws \Exception
     */
    public function index($rootValue, array $args, AppContext $context, ResolveInfo $resolveInfo): array
    {
        $perPage = $args['first'] ?? 100;

        $source = $this->getSource($context, $args);

        $calendars = $source->getCalendars();

        $count = $calendars->count();
        $currentPage = $args['page'] ?? 1;
        $firstItem = $count > 0 ? ($currentPage - 1) * $perPage + 1 : null;
        $lastItem = $count > 0 ? $firstItem + $count - 1 : null;

        return [
            'data' => $calendars,
            'paginatorInfo' => [
                'count' => $count,
                'currentPage' => $currentPage,
                'firstItem' => $firstItem,
                'lastItem' => $lastItem,
                'perPage' => $perPage,
            ],
        ];
    }

    /**
     * @param  null  $rootValue
     *
     * @throws \Exception
     */
    public function store($rootValue, array $args, AppContext $context, ResolveInfo $resolveInfo): array
    {
        $data = Arr::only($args['input'], ['name']);

        $source = $this->getSource($context, $args);

        if ($source->getCalendarByName($data['name'])) {
            throw ValidationException::withMessages(['input.name' => trans('validation.custom.calendar.name.unique')]);
        }

        $calendar = $source->createCalendar($data['name']);

        return $this->mutationResponse(200, 'External calendar was created successfully', [
            'calendar' => $calendar,
        ]);
    }

    /**
     * @param  null  $rootValue
     *
     * @throws \Exception
     */
    public function update($rootValue, array $args, AppContext $context, ResolveInfo $resolveInfo): array
    {
        $source = $this->getSource($context, $args);

        $calendar = $source->getCalendarByName($args['input']['name']);
        if ($calendar && $calendar->id !== $args['input']['id']) {
            throw ValidationException::withMessages(['input.name' => trans('validation.custom.calendar.name.unique', ['list' => 'calendar'])]);
        }

        $calendar = $source->updateCalendar($args['input']['id'], $args['input']['name'], $args['input']['color'] ?? null);

        return $this->mutationResponse(200, 'External calendar was updated successfully', [
            'calendar' => $calendar,
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

        $source->deleteCalendar($args['input']['id']);

        return $this->mutationResponse(200, 'External calendar was deleted successfully');
    }

    public function colorOrDefault(IntegrationCalendar $root): Color
    {
        $color = $root->color ?? null;
        if (! $color) {
            $defaultHex = config('planner.events.default_color', '#AEAEAE');

            return Color::make($defaultHex);
        }

        return Color::make($color);
    }

    protected function getSource(AppContext $context, array $args): IntegrationAccount
    {
        return $this->baseGetSource($context, $args, Scope::CALENDAR);
    }
}
