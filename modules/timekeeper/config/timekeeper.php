<?php

declare(strict_types=1);

use Timekeeper\Models\Deadline;
use Timekeeper\Models\DeadlineGroup;

return [
    'models' => [
        'deadline_group' => DeadlineGroup::class,
        'deadline' => Deadline::class,
    ],
];
