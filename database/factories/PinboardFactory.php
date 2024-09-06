<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Pin;
use App\Models\Pinboard;

class PinboardFactory extends FeatureListFactory
{
    protected $model = Pinboard::class;

    public function withPins(?int $count = null): self
    {
        return $this->has(Pin::factory()->count($count ?: $this->faker->numberBetween(5, 10)));
    }

    public function withChildren(?int $count = null): self
    {
        return $this->withPins($count);
    }
}
