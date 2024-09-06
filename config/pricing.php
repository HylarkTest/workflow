<?php

declare(strict_types=1);

return [
    'plans' => [
        // The premium plan for personal bases.
        'ascend' => [
            'yearly_stripe_id' => env('ASCEND_YEARLY_ID'),
            'monthly_stripe_id' => env('ASCEND_MONTHLY_ID'),
        ],
        // The premium plan for collaborative bases.
        'soar' => [
            'yearly_stripe_id' => env('SOAR_YEARLY_ID'),
            'monthly_stripe_id' => env('SOAR_MONTHLY_ID'),
        ],
    ],
];
