<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Marker;
use App\Models\MarkerGroup;

class MarkerFactory extends \Markers\Database\Factories\MarkerFactory
{
    protected $model = Marker::class;

    public function definition(): array
    {
        return [
            'name' => ucfirst($this->faker->word),
            'color' => $this->faker->hexColor,
            'marker_group_id' => MarkerGroup::factory(),
        ];
    }
}
