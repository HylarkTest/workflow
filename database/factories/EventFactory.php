<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;

class EventFactory extends Factory
{
    public function __construct($count = null, ?Collection $states = null, ?Collection $has = null, ?Collection $for = null, ?Collection $afterMaking = null, ?Collection $afterCreating = null, $connection = null)
    {
        $this->model = config('planner.models.event');
        parent::__construct($count, $states, $has, $for, $afterMaking, $afterCreating, $connection);
    }

    public function definition(): array
    {
        $start = $this->faker->dateTime;
        $end = $this->faker->dateTimeBetween($start, $start->add(new \DateInterval('P3D')));

        return [
            'uuid' => $this->faker->uuid,
            'name' => ucfirst($this->faker->word),
            'description' => $this->faker->optional()->paragraph,
            'start_at' => $start,
            'end_at' => $end,
            'timezone' => $this->faker->timezone,
            'recurrence' => $this->faker->optional()->recurrence,
            'priority' => $this->faker->optional()->numberBetween(1, 5),
            'is_all_day' => $this->faker->boolean,
            'location' => $this->faker->optional()->city,
            'calendar_id' => config('planner.models.calendar')::factory(),
        ];
    }
}
