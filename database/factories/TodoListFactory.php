<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Support\Collection;

class TodoListFactory extends FeatureListFactory
{
    public function __construct($count = null, ?Collection $states = null, ?Collection $has = null, ?Collection $for = null, ?Collection $afterMaking = null, ?Collection $afterCreating = null, $connection = null)
    {
        $this->model = config('planner.models.todo_list');
        parent::__construct($count, $states, $has, $for, $afterMaking, $afterCreating, $connection);
    }

    public function withTodos(?int $count = null): self
    {
        return $this->has(config('planner.models.todo')::factory()->count($count ?: $this->faker->numberBetween(5, 10)));
    }

    public function withChildren(?int $count = null): self
    {
        return $this->withTodos($count);
    }
}
