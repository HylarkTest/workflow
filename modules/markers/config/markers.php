<?php

declare(strict_types=1);

use Markers\Models\Marker;
use Markers\Models\MarkerGroup;

return [
    'models' => [
        'marker_group' => MarkerGroup::class,
        'marker' => Marker::class,
    ],
];
