<?php

declare(strict_types=1);

use App\Models\Marker;
use App\Models\MarkerGroup;

return [
    'models' => [
        'marker_group' => MarkerGroup::class,
        'marker' => Marker::class,
    ],
];
