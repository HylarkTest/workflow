<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Mapping;
use Illuminate\Support\Str;
use Mappings\Core\Mappings\Fields\FieldType;
use Illuminate\Database\Eloquent\Factories\Factory;

class MappingFactory extends Factory
{
    protected $model = Mapping::class;

    public function definition(): array
    {
        $name = ucfirst($this->faker->word);

        return [
            'name' => Str::plural($name),
            'type' => $this->faker->boolean ? 'ITEM' : 'PERSON',
            'description' => ucfirst($this->faker->paragraph),
            'features' => [],
            'fields' => [
                [
                    'id' => 'name',
                    'name' => 'Name',
                    'type' => FieldType::SYSTEM_NAME(),
                ],
                $this->faker->mappingField,
            ],
        ];
    }
}
