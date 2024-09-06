<?php

declare(strict_types=1);

namespace Timekeeper\Database\Factories;

use Timekeeper\Models\Deadline;
use Timekeeper\Models\DeadlineGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

class DeadlineGroupFactory extends Factory
{
    protected $model = DeadlineGroup::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->word,
            'description' => $this->faker->sentence,
        ];
    }

    public function withDeadlines(): self
    {
        return $this->has(Deadline::factory()->count($this->faker->numberBetween(5, 20)));
    }
}
