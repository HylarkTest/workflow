<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

abstract class FeatureListFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => ucfirst($this->faker->word),
            'color' => $this->faker->hexColor,
        ];
    }

    abstract public function withChildren(?int $count = null): self;
}
