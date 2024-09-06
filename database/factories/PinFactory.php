<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Pin;
use App\Models\Image;
use App\Models\Pinboard;
use Illuminate\Database\Eloquent\Factories\Factory;

class PinFactory extends Factory
{
    protected $model = Pin::class;

    public function definition(): array
    {
        return [
            'name' => ucfirst($this->faker->word),
            'description' => $this->faker->optional()->paragraph,
            'document_id' => Image::factory(),
            'favorited_at' => null,
            'pinboard_id' => Pinboard::factory(),
        ];
    }
}
