<?php

declare(strict_types=1);

namespace Database\Factories\Support;

use App\Models\Support\SupportFolder;
use App\Models\Support\SupportCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

class SupportFolderFactory extends Factory
{
    protected $model = SupportFolder::class;

    public function definition(): array
    {
        return [
            'name' => ucfirst($this->faker->word),
            'category_id' => SupportCategory::factory(),
        ];
    }
}
