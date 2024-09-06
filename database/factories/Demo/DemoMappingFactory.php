<?php

declare(strict_types=1);

namespace Database\Factories\Demo;

use Database\Factories\MappingFactory;

class DemoMappingFactory extends MappingFactory
{
    public function people(): self
    {
        return $this->state([
            'name' => 'People',
            'singular_name' => 'Person',
            'type' => 'PERSON',
            'description' => 'People who work on projects',
            'features' => [
                ['type' => 'NOTES'],
                ['type' => 'PLANNER'],
            ],
            'fields' => [
                [
                    'id' => 'titleId',
                    'name' => 'Title',
                    'type' => 'TITLE',
                    'options' => [],
                    'rules' => ['required' => true, 'max' => 20],
                ],
                [
                    'id' => 'imageId',
                    'name' => 'Profile picture',
                    'type' => 'IMAGE',
                    'options' => ['croppable' => true],
                ],
                [
                    'id' => 'emailId',
                    'name' => 'E-mail',
                    'type' => 'EMAIL',
                    'options' => [
                        'labeled' => [
                            'labels' => ['Private', 'Work'],
                        ],
                        'rules' => [
                            'required' => true,
                        ],
                    ],
                ],
                [
                    'id' => 'addressId',
                    'name' => 'Address',
                    'type' => 'ADDRESS',
                    'options' => [
                        'labeled' => [
                            'labels' => ['Private', 'Work'],
                        ],
                    ],
                ],
                [
                    'id' => 'companyId',
                    'name' => 'Company',
                    'type' => 'LINE',
                ],
            ],
            'design' => [
                'icon' => 'fa-users',
            ],
        ]);
    }

    public function projects(): self
    {
        return $this->state([
            'name' => 'Projects',
            'singular_name' => 'Project',
            'type' => 'ITEM',
            'description' => 'Projects that people work on',
            'features' => [],
            'fields' => [
                [
                    'id' => 'description',
                    'name' => 'Description',
                    'type' => 'PARAGRAPH',
                ],
            ],
            'design' => json_decode(<<<'JSON'
{
    "icon": "fa-chart-pie"
}
JSON, true, 512, \JSON_THROW_ON_ERROR),
        ]);
    }
}
