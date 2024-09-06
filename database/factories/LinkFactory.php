<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Link;
use App\Models\LinkList;
use Illuminate\Database\Eloquent\Factories\Factory;

class LinkFactory extends Factory
{
    protected $model = Link::class;

    public function definition(): array
    {
        return [
            'name' => ucfirst($this->faker->word),
            'description' => $this->faker->optional()->paragraph,
            'url' => $this->faker->url,
            'favorited_at' => null,
            'link_list_id' => LinkList::factory(),
        ];
    }
}
