<?php

declare(strict_types=1);

namespace Database\Providers;

use Faker\Provider\Base;

class EnumProvider extends Base
{
    public function enum(string $class): string
    {
        return static::randomElement($class::cases());
    }
}
