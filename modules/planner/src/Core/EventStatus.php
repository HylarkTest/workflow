<?php

declare(strict_types=1);

namespace Planner\Core;

enum EventStatus: string
{
    case TENTATIVE = 'TENTATIVE';
    case CONFIRMED = 'CONFIRMED';
    case CANCELLED = 'CANCELLED';
}
