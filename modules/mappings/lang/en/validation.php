<?php

declare(strict_types=1);

return [
    'attributes' => [
        'field' => [
            'name' => 'name',
            'apiName' => 'API name',
            'options' => [
                'rules' => [
                    'max' => 'max rule',
                    'maxText' => 'max text rule',
                    'requiredFields' => 'requiredField',
                    'before' => 'before rule',
                    'after' => 'after rule',
                    'extensions' => 'extensions',
                ],
                'only' => 'field',
                'only.*' => 'field',
                'category' => 'category',
                'isRange' => 'isRange',
            ],
            'type' => 'type',
            'meta' => 'meta',
        ],
        'relationship' => [
            'name' => 'name',
            'to' => 'The related mapping is required',
        ],
        'label' => 'label',
    ],
    'rules' => [
        'currency' => 'The selected :attribute is not a supported currency code.',
        'max_difference' => 'The :attribute must be :difference :date.',
    ],
];
