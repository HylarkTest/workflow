<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Image;
use Illuminate\Database\Eloquent\Factories\Factory;

class ImageFactory extends Factory
{
    protected $model = Image::class;

    public function definition(): array
    {
        $extension = $this->faker->randomElement(['jpeg', 'png', 'gif', 'bmp', 'webp']);

        return [
            'filename' => $this->faker->word.'.'.$extension,
            'size' => $this->faker->numberBetween(1000, 1000000),
            'extension' => $extension,
            'url' => Image::directory().'/'.$this->faker->lexify('??????').'.'.$extension,
        ];
    }
}
