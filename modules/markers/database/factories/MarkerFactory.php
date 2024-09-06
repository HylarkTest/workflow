<?php

declare(strict_types=1);

namespace Markers\Database\Factories;

use Markers\Models\Marker;
use Markers\Models\MarkerGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

class MarkerFactory extends Factory
{
    protected $model = Marker::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->word,
            'color' => $this->faker->hexColor,
            'marker_group_id' => fn () => MarkerGroup::factory(),
        ];
    }
}
