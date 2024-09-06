<?php

declare(strict_types=1);

return [
    'attributes' => [
        'input' => [
            'name' => 'name',
            'apiName' => 'API name',
            'options' => [
                'rules' => [
                    'max' => 'max rule',
                    'maxText' => 'max text rule',
                    'requiredFields.*' => 'required field',
                    'before' => 'before rule',
                    'after' => 'after rule',
                    'extensions' => 'extensions',
                ],
                'only' => 'field',
                'only.*' => 'field',
                'category' => 'category',
                'isRange' => 'isRange',
                'levels.*' => 'level',
                'countries.*' => 'country',
                'fields' => 'sub fields',
                'labeled' => [
                    'labels' => 'labels',
                    'labels.*' => 'labels',
                ],
            ],
        ],
        'relationship' => [
            'name' => 'name',
            'to' => 'related page',
        ],
        'tagGroup' => [
            'name' => 'name',
            'apiName' => 'API name',
            'group' => 'tag collection',
        ],
        'label' => 'label',
    ],
    'rules' => [
        'currency' => 'The selected :attribute is not a supported currency code.',
        'max_difference' => 'The :attribute must be :difference :date.',
        'single_main' => 'Only one item can be marked as main.',
    ],
    'field_options' => [
        'input' => [
            'options.labeled.labels' => [
                'required_if' => 'Please provide label options.',
            ],
            'options.labeled.labels.*' => [
                'required_if' => 'Please provide label options.',
            ],
        ],
    ],
];
