<?php

declare(strict_types=1);

namespace App\Core\Preferences;

use Illuminate\Support\Carbon;

enum DateFormat: string
{
    public function formatString(): string
    {
        return match ($this) {
            self::DAY_MONTH_YEAR => 'd/m/Y',
            self::MONTH_DAY_YEAR => 'm/d/Y',
            self::YEAR_MONTH_DAY => 'Y/m/d',
        };
    }

    public function format(Carbon $date): string
    {
        return $date->format($this->formatString());
    }
    case DAY_MONTH_YEAR = 'DMY';
    case MONTH_DAY_YEAR = 'MDY';
    case YEAR_MONTH_DAY = 'YMD';
}
