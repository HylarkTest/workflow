<?php

declare(strict_types=1);

namespace App\Core\Preferences;

enum TimeFormat: string
{
    case TWELVE_HOUR = '12';
    case TWENTY_FOUR_HOUR = '24';
}
