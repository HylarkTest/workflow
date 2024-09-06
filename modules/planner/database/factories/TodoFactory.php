<?php

declare(strict_types=1);

namespace Planner\Database\Factories;

use App\Models\TodoList;
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
            'description' => $this->faker->paragraph,
            'priority' => $this->faker->numberBetween(1, 5),
            'due_by' => $this->faker->dateTime,
            'todo_list_id' => TodoList::factory(),
        ];
    }
}
