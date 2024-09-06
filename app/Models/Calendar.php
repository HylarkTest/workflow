<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Contracts\FeatureList;
use Planner\Models\Calendar as BaseCalendar;
use App\Models\Concerns\HasFeatureListMethods;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Calendar
 *
 * @method \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Event> events()
 *
 * @implements \App\Models\Contracts\FeatureList<\App\Models\Event, \App\Models\Calendar>
 */
class Calendar extends BaseCalendar implements FeatureList
{
    /** @use \App\Models\Concerns\HasFeatureListMethods<\App\Models\Event, \App\Models\Calendar> */
    use HasFeatureListMethods;

    protected $fillable = [
        'space_id',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Event>
     */
    public function children(): HasMany
    {
        return $this->events();
    }
}
