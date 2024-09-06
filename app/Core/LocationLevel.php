<?php

declare(strict_types=1);

namespace App\Core;

use Illuminate\Support\Arr;

enum LocationLevel: int
{
    public static function fromNames(array $names): array
    {
        $cases = self::cases();

        return array_map(function (string $name) use ($cases) {
            return Arr::first($cases, fn (LocationLevel $level) => $level->name === $name);
        }, $names);
    }

    case CONTINENT = 0;
    case COUNTRY = 1;
    case STATE = 2;
    case CITY = 3;
}
