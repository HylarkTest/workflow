<?php

declare(strict_types=1);

namespace Markers\Database\Factories;

use Markers\Models\Marker;
use Markers\Models\MarkerGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

class MarkerGroupFactory extends Factory
{
    protected $model = MarkerGroup::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->word,
            'description' => $this->faker->sentence,
        ];
    }

    public function withMarkers(): MarkerGroupFactory
    {
        return $this->has(Marker::factory()->count($this->faker->numberBetween(5, 20)));
    }
}
