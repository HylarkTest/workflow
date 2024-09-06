<?php

declare(strict_types=1);

namespace Planner\Core;

enum TodoStatus: string
{
    case NEEDS_ACTION = 'NEEDS-ACTION';
    case COMPLETED = 'COMPLETED';
    case IN_PROCESS = 'IN-PROGRESS';
    case CANCELLED = 'CANCELLED';
}
