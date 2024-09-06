<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Base;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Base>
 */
class BaseFactory extends Factory
{
    protected $model = Base::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name,
        ];
    }
}
