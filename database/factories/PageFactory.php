<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Page;
use App\Core\Pages\PageType;
use Illuminate\Database\Eloquent\Factories\Factory;

class PageFactory extends Factory
{
    protected $model = Page::class;

    public function definition(): array
    {
        return [
            'path' => ucfirst($this->faker->word),
            'symbol' => 'fa-lemon',
            'type' => PageType::ENTITIES,
        ];
    }
}
