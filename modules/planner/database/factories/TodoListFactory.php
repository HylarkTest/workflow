<?php

declare(strict_types=1);

namespace Planner\Database\Factories;

use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;

class TodoListFactory extends Factory
{
    public function __construct($count = null, ?Collection $states = null, ?Collection $has = null, ?Collection $for = null, ?Collection $afterMaking = null, ?Collection $afterCreating = null, $connection = null)
    {
        $this->model = config('planner.models.todo_list');
        parent::__construct($count, $states, $has, $for, $afterMaking, $afterCreating, $connection);
    }

    public function definition(): array
    {
        return [
            'name' => ucfirst($this->faker->word),
        ];
    }

    public function withTodos(): self
    {
        return $this->has(config('planner.models.todo')::factory()->count($this->faker->numberBetween(5, 10)));
    }
}
