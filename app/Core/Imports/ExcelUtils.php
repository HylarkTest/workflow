<?php

declare(strict_types=1);

namespace App\Core\Imports;

use Illuminate\Support\Carbon;

class ExcelUtils
{
    public static function convertDateCellToCarbon(string|int|float $dateString): Carbon
    {
        if (! is_numeric($dateString)) {
            throw new \InvalidArgumentException('Invalid date string');
        }
        $daysSince1900 = (float) $dateString;
        // Excel incorrectly considers 1900 a leap year
        if ($daysSince1900 > 60) {
            $daysSince1900--;
        }
        /** @var \Illuminate\Support\Carbon $date */
        $date = Carbon::create(1899, 12, 31, 0, 0, 0);
        $days = (int) floor($daysSince1900);
        $milliseconds = (int) (($daysSince1900 - $days) * 24 * 60 * 60 * 1000);

        return $date->addDays($days)->addMilliseconds($milliseconds);
    }
}
