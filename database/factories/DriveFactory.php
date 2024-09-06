<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Drive;
use App\Models\Document;

class DriveFactory extends FeatureListFactory
{
    protected $model = Drive::class;

    public function withDocuments(?int $count = null): self
    {
        return $this->has(
            Document::factory()
                ->count($count ?: $this->faker->numberBetween(5, 10)),
        );
    }

    public function withChildren(?int $count = null): self
    {
        return $this->withDocuments($count);
    }
}
