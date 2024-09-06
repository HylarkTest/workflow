<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Note;
use App\Models\Notebook;

class NotebookFactory extends FeatureListFactory
{
    protected $model = Notebook::class;

    public function withNotes(?int $count = null): self
    {
        return $this->has(
            Note::factory()
                ->count($count ?: $this->faker->numberBetween(5, 10))
        );
    }

    public function withChildren(?int $count = null): self
    {
        return $this->withNotes($count);
    }
}
