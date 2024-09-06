<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Space;
use Illuminate\Database\Eloquent\Factories\Factory;

class SpaceFactory extends Factory
{
    protected $model = Space::class;

    public function definition(): array
    {
        return [
            'name' => ucfirst($this->faker->word),
            'color' => $this->faker->boolean ? null : $this->faker->tailwindColor,
            'logo' => $this->faker->imageUrl(640, 480, 'abstract'),
        ];
    }

    public function logo(): self
    {
        return $this->state(['logo' => $this->faker->storedLogo()]);
    }
}
