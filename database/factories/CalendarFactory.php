<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Support\Collection;

class CalendarFactory extends FeatureListFactory
{
    public function __construct($count = null, ?Collection $states = null, ?Collection $has = null, ?Collection $for = null, ?Collection $afterMaking = null, ?Collection $afterCreating = null, $connection = null)
    {
        $this->model = config('planner.models.calendar');
        parent::__construct($count, $states, $has, $for, $afterMaking, $afterCreating, $connection);
    }

    public function withEvents(?int $count = null): self
    {
        return $this->has(
            config('planner.models.event')::factory()
                ->count($count ?: $this->faker->numberBetween(5, 10))
        );
    }

    public function withChildren(?int $count = null): self
    {
        return $this->withEvents($count);
    }
}
