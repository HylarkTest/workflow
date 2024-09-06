<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Note;
use App\Models\Notebook;
use MarkupUtils\Plaintext;
use Illuminate\Database\Eloquent\Factories\Factory;

class NoteFactory extends Factory
{
    protected $model = Note::class;

    public function definition(): array
    {
        return [
            'name' => ucfirst($this->faker->word),
            'text' => new Plaintext($this->faker->paragraphs(3, true)),
            'favorited_at' => null,
            'notebook_id' => Notebook::factory(),
        ];
    }
}
