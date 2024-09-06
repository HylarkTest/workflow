<?php

declare(strict_types=1);

namespace Database\Factories\Support;

use App\Models\Support\ArticleStatus;
use App\Models\Support\SupportFolder;
use App\Models\Support\SupportArticle;
use Illuminate\Database\Eloquent\Factories\Factory;

class SupportArticleFactory extends Factory
{
    protected $model = SupportArticle::class;

    public function definition(): array
    {
        return [
            'title' => ucfirst($this->faker->word),
            'status' => ArticleStatus::PUBLISHED,
            'folder_id' => SupportFolder::factory(),
            'content' => $this->faker->randomHtml(),
            'live_at' => now(),
        ];
    }
}
