<?php

declare(strict_types=1);

namespace App\GraphQL\Queries\Features;

use App\Models\Base;
use App\Core\Features\Repositories\CalendarRepository;

/**
 * @extends \App\GraphQL\Queries\Features\FeatureListQuery<\App\Models\Calendar>
 */
class CalendarQuery extends FeatureListQuery
{
    protected function repository(): CalendarRepository
    {
        return resolve(CalendarRepository::class);
    }

    protected function createDefaultLists(Base $base): void
    {
        $base->createDefaultCalendars();
    }

    protected function getListKey(): string
    {
        return 'calendar';
    }
}
