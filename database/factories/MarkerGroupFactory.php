<?php

declare(strict_types=1);

namespace Database\Factories;

use Markers\Models\Marker;
use App\Models\MarkerGroup;
use Markers\Core\MarkerType;

class MarkerGroupFactory extends \Markers\Database\Factories\MarkerGroupFactory
{
    protected $model = MarkerGroup::class;

    public function definition(): array
    {
        return [
            'name' => ucfirst($this->faker->word),
            'type' => MarkerType::STATUS,
            'description' => $this->faker->paragraph,
        ];
    }

    public function withMarkers(?int $count = null): self
    {
        return $this->has(Marker::factory()->count($count ?: $this->faker->numberBetween(5, 10)));
    }
}
