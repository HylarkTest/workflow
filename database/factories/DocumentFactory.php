<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Drive;
use App\Models\Document;
use Illuminate\Database\Eloquent\Factories\Factory;

class DocumentFactory extends Factory
{
    protected $model = Document::class;

    public function definition(): array
    {
        $extension = $this->faker->fileExtension();

        return [
            'filename' => $this->faker->word.'.'.$extension,
            'size' => $this->faker->numberBetween(1000, 1000000),
            'extension' => $extension,
            'url' => Document::directory().'/'.$this->faker->lexify('??????').'.'.$extension,
            'favorited_at' => null,
            'drive_id' => Drive::factory(),
        ];
    }
}
