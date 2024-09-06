<?php

declare(strict_types=1);

namespace Database\Providers;

use Faker\Provider\Base;

class TailwindProvider extends Base
{
    public function tailwindColor()
    {
        return $this->randomElement([
            'gray',
            'red',
            'orange',
            'yellow',
            'green',
            'teal',
            'blue',
            'indigo',
            'purple',
            'pink',
        ]);
    }
}
