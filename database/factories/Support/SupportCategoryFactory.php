<?php

declare(strict_types=1);

namespace Database\Factories\Support;

use App\Models\Support\SupportCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

class SupportCategoryFactory extends Factory
{
    protected $model = SupportCategory::class;

    public function definition(): array
    {
        return [
            'name' => ucfirst($this->faker->word),
        ];
    }
}
