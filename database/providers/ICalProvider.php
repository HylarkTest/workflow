<?php

declare(strict_types=1);

namespace Database\Providers;

use Faker\Provider\Base;

class ICalProvider extends Base
{
    public function recurrence(): ?array
    {
        $weekDays = ['MO', 'TU', 'WE', 'TH', 'FR', 'SA', 'SU'];
        $recurrence = [
            'FREQ' => static::randomElement(['DAILY', 'WEEKLY', 'MONTHLY', 'YEARLY']),
            'INTERVAL' => static::randomNumber(),
            'BYDAY' => static::randomElement($weekDays),
        ];

        if (static::randomNumber(1) === 0) {
            if (static::randomNumber(1) === 0) {
                $recurrence['COUNT'] = static::randomNumber();
            } else {
                $recurrence['UNTIL'] = $this->generator->dateTimeBetween('now', '+1 year')->format('Ymd\THis\Z');
            }
        }

        return $recurrence;
    }
}
