<?php

declare(strict_types=1);

namespace Timekeeper\Database\Factories;

use Timekeeper\Models\Deadline;
use Timekeeper\Models\DeadlineGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

class DeadlineFactory extends Factory
{
    protected $model = Deadline::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->word,
            'color' => $this->faker->hexColor,
            'deadline_group_id' => fn () => DeadlineGroup::factory(),
        ];
    }
}
