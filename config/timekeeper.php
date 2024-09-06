<?php

declare(strict_types=1);

use App\Models\Deadline;
use App\Models\DeadlineGroup;

return [
    'models' => [
        'deadline_group' => DeadlineGroup::class,
        'deadline' => Deadline::class,
    ],
];
