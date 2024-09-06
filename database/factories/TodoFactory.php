<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;

class TodoFactory extends Factory
{
    public function __construct($count = null, ?Collection $states = null, ?Collection $has = null, ?Collection $for = null, ?Collection $afterMaking = null, ?Collection $afterCreating = null, $connection = null)
    {
        $this->model = config('planner.models.todo');
        parent::__construct($count, $states, $has, $for, $afterMaking, $afterCreating, $connection);
    }

    public function definition(): array
    {
        return [
            'name' => ucfirst($this->faker->word),
            'description' => $this->faker->optional()->paragraph,
            'completed_at' => null,
            'priority' => $this->faker->numberBetween(1, 5),
            'due_by' => $this->faker->optional()->dateTime,
            'recurrence' => null,
            'location' => $this->faker->optional()->city(),
            'todo_list_id' => config('planner.models.todo_list')::factory(),
        ];
    }

    public function optionalCompleted(): self
    {
        return $this->state(function () {
            return [
                'completed_at' => $this->faker->optional()->dateTime,
            ];
        });
    }

    public function optionalRecurrence(): self
    {
        return $this->state(function () {
            return [
                'recurrence' => $this->faker->optional()->recurrence,
            ];
        });
    }
}
