<?php

declare(strict_types=1);

namespace Mappings\Database\Factories;

use Mappings\Models\Mapping;
use Mappings\Core\Mappings\Fields\FieldType;
use Mappings\Database\Providers\MappingProvider;
use Illuminate\Database\Eloquent\Factories\Factory;

class MappingFactory extends Factory
{
    protected $model = Mapping::class;

    public function definition()
    {
        $this->faker->addProvider(new MappingProvider($this->faker));

        return [
            'name' => $this->faker->word,
            'singular_name' => $this->faker->word,
            'api_name' => $this->faker->word,
            'api_singular_name' => $this->faker->word,
            'type' => 'ITEM',
            'description' => $this->faker->paragraph,
            'fields' => [
                [
                    'id' => 'name',
                    'name' => 'Name',
                    'type' => FieldType::NAME(),
                ],
                $this->faker->mappingField,
            ],
        ];
    }

    public function contact(): static
    {
        return $this->state([
            'name' => 'Contacts',
            'singular_name' => 'Contact',
            'type' => 'PERSON',
            'description' => 'People we are in contact with',
            'fields' => [
                [
                    'name' => 'Name',
                    'type' => 'LINE',
                    'options' => [
                    ],
                    'rules' => [
                        'required' => true,
                        'max' => 100,
                    ],
                ],
                [
                    'name' => 'Profile picture',
                    'type' => 'IMAGE',
                ],
                [
                    'name' => 'Work',
                    'type' => 'MULTI',
                    'options' => [
                        'fields' => [
                            [
                                'name' => 'Company',
                                'type' => 'LINE',
                            ],
                            [
                                'name' => 'Job title',
                                'type' => 'LINE',
                            ],
                        ],
                    ],
                ],
                [
                    'name' => 'E-mail',
                    'type' => 'EMAIL',
                    'options' => [
                        'list' => ['max' => 5],
                        'labeled' => ['labels' => ['Private', 'Work']],
                    ],
                ],
                [
                    'name' => 'Phone number',
                    'type' => 'PHONE',
                    'options' => [
                        'list' => ['max' => 5],
                        'labeled' => ['labels' => ['Private', 'Work']],
                    ],
                ],
                [
                    'name' => 'Address',
                    'type' => 'ADDRESS',
                    'options' => [
                        'list' => ['max' => 5],
                        'labeled' => ['labels' => ['Private', 'Work']],
                    ],
                ],
                [
                    'name' => 'Birthday',
                    'type' => 'DATE',
                ],
                [
                    'name' => 'Website',
                    'type' => 'URL',
                    'options' => [
                        'list' => ['max' => 5],
                        'labeled' => ['labels' => ['Private', 'Work']],
                    ],
                ],
            ],
        ]);
    }
}
