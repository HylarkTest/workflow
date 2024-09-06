<?php

declare(strict_types=1);

namespace App\Core\Preferences;

enum WeekStart: int
{
    case MONDAY = 0;
    case TUESDAY = 1;
    case WEDNESDAY = 2;
    case THURSDAY = 3;
    case FRIDAY = 4;
    case SATURDAY = 5;
    case SUNDAY = 6;
}
