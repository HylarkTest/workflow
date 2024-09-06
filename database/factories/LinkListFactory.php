<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Link;
use App\Models\LinkList;

class LinkListFactory extends FeatureListFactory
{
    protected $model = LinkList::class;

    public function withLinks(?int $count = null): self
    {
        return $this->has(
            Link::factory()->count(
                $count ?: $this->faker->numberBetween(5, 10)
            )
        );
    }

    public function withChildren(?int $count = null): self
    {
        return $this->withLinks($count);
    }
}
