<?php

declare(strict_types=1);

namespace Database\Factories\Demo;

use Database\Factories\MappingFactory;

class EzekiaMappingFactory extends MappingFactory
{
    public function projects(): self
    {
        return $this->state([
            'name' => 'Projects',
            'singular_name' => 'Project',
            'type' => 'ITEM',
            'description' => 'Projects being worked on',
            'features' => [
                ['type' => 'PLANNER'],
                ['type' => 'NOTES'],
                ['type' => 'COLLABORATION'],
            ],
            'fields' => [
                [
                    'id' => 'projectIdId',
                    'name' => 'Project ID',
                    'type' => 'LINE',
                ],
                [
                    'id' => 'descriptionId',
                    'name' => 'Description',
                    'type' => 'PARAGRAPH',
                ],
            ],
            'design' => [
                'icon' => 'fa-list-ul',
            ],
        ]);
    }

    public function people(): self
    {
        return $this->state([
            'name' => 'People',
            'singular_name' => 'People',
            'type' => 'PERSON',
            'description' => 'All contacts and candidates',
            'features' => [
                ['type' => 'NOTES'],
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
                    'id' => 'pictureId',
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
                    'id' => 'locationId',
                    'name' => 'Location',
                    'type' => 'LINE',
                ],
                [
                    'id' => 'confidentialId',
                    'name' => 'Confidential',
                    'type' => 'MULTI',
                    'options' => [
                        'fields' => [
                            [
                                'id' => 'confidential_salaryId',
                                'name' => 'Salary',
                                'type' => 'LINE',
                            ],
                            [
                                'id' => 'confidential_bonusId',
                                'name' => 'Bonus',
                                'type' => 'LINE',
                            ],
                        ],
                    ],
                ],
                [
                    'id' => 'positionsId',
                    'name' => 'Positions',
                    'type' => 'MULTI',
                    'options' => [
                        'list' => ['max' => 10, 'oneRequired' => true],
                        'fields' => [
                            [
                                'id' => 'position_companyId',
                                'name' => 'Company',
                                'type' => 'LINE',
                            ],
                            [
                                'id' => 'position_titleId',
                                'name' => 'Title',
                                'type' => 'LINE',
                            ],
                            [
                                'id' => 'position_summaryId',
                                'name' => 'Summary',
                                'type' => 'PARAGRAPH',
                            ],
                            [
                                'id' => 'position_skillsId',
                                'name' => 'Skills',
                                'type' => 'LINE',
                                'options' => ['list' => ['max' => 3]],
                            ],
                            [
                                'id' => 'position_achievementsId',
                                'name' => 'Achievements',
                                'type' => 'LINE',
                                'options' => ['list' => ['max' => 3]],
                            ],
                        ],
                    ],
                ],
            ],
            'design' => [
                'icon' => 'fa-users',
            ],
        ]);
    }

    public function companies(): self
    {
        return $this->state([
            'name' => 'Clients',
            'singular_name' => 'Client',
            'type' => 'ITEM',
            'description' => 'Clients we are working with',
            'features' => [
                ['type' => 'NOTES'],
                ['type' => 'PLANNER'],
                ['type' => 'COLLABORATION'],
                ['type' => 'COMMENTS'],
            ],
            'fields' => [
                [
                    'id' => 'divisionId',
                    'name' => 'Division / Subsidiary',
                    'type' => 'LINE',
                ],
                [
                    'id' => 'descriptionId',
                    'name' => 'Description',
                    'type' => 'PARAGRAPH',
                ],
                [
                    'id' => 'specialtiesId',
                    'name' => 'Specialties',
                    'type' => 'PARAGRAPH',
                ],
                [
                    'id' => 'emailId',
                    'name' => 'email',
                    'type' => 'EMAIL',
                ],
                [
                    'id' => 'sizeId',
                    'name' => 'Size',
                    'type' => 'LINE',
                ],
                [
                    'id' => 'industryId',
                    'name' => 'Industry',
                    'type' => 'LINE',
                ],
                [
                    'id' => 'logoId',
                    'name' => 'Logo',
                    'type' => 'IMAGE',
                    'options' => ['croppable' => true],
                ],
                [
                    'id' => 'influenceId',
                    'name' => 'Influence',
                    'type' => 'LINE',
                ],
                [
                    'id' => 'addressId',
                    'name' => 'Address',
                    'type' => 'ADDRESS',
                ],
            ],
            'design' => [
                'icon' => 'fa-building',
            ],
        ]);
    }

    public function ideas(): EzekiaItemFactory
    {
        return $this->state([
            'name' => 'Ideas',
            'singular_name' => 'Idea',
            'type' => 'ITEM',
            'description' => 'My ideas',
            'features' => [
                ['type' => 'PLANNER'],
                ['type' => 'NOTES'],
            ],
            'fields' => [
                [
                    'id' => 'imageId',
                    'name' => 'Image',
                    'type' => 'IMAGE',
                    'options' => ['croppable' => true],
                ],
            ],
            'design' => json_decode(/* @lang json */ '
    {
        "icon": "fa-lightbulb",
        "item": {
            "selected": {
                "id": "g",
                "rows": [
                    {
                        "containers": [
                            {
                                "id": "r",
                                "data": "imageId",
                                "type": "GRAPHIC",
                                "style": {
                                    "size": "xl",
                                    "shape": "circle"
                                },
                                "subType": null,
                                "category": "FIELDS"
                            }
                        ]
                    },
                    {
                        "containers": [
                            {
                                "id": "b",
                                "data": "name",
                                "type": "CONTENT",
                                "style": {
                                    "color": "light",
                                    "weight": "bold"
                                },
                                "subType": null,
                                "category": "FIELDS"
                            },
                            {
                                "id": "f",
                                "data": null,
                                "type": "CONTENT",
                                "style": {
                                    "weight": "bold"
                                },
                                "subType": null,
                                "category": null
                            }
                        ]
                    },
                    {
                        "containers": [
                            {
                                "id": "a",
                                "data": null,
                                "type": "CONTENT",
                                "style": [

                                ],
                                "subType": null,
                                "category": null
                            }
                        ]
                    },
                    {
                        "containers": [
                            {
                                "id": "b",
                                "data": null,
                                "type": "CONTENT",
                                "style": [

                                ],
                                "subType": null,
                                "category": null
                            }
                        ]
                    }
                ],
                "style": "card",
                "labels": {
                    "on": "contentOnly",
                    "style": {
                        "case": "uppercase",
                        "size": "xxs",
                        "color": "light",
                        "weight": "bold"
                    }
                }
            }
        }
    }
    ', true, 512, \JSON_THROW_ON_ERROR),
        ]);
    }
}
