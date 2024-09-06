<?php

declare(strict_types=1);

namespace App\Core;

enum TaskStatus: string
{
    case STARTED = 'STARTED';
    case COMPLETED = 'COMPLETED';
    case FAILED = 'FAILED';
    case CANCELLED = 'CANCELLED';
    case CANCELLING = 'CANCELLING';
    case REVERTING = 'REVERTING';
    case REVERTED = 'REVERTED';
}
