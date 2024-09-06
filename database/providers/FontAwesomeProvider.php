<?php

declare(strict_types=1);

namespace Database\Providers;

use Faker\Provider\Base;

class FontAwesomeProvider extends Base
{
    protected static array $icons = [
        'abacus', 'acorn', 'alicorn', 'bullhorn', 'brain', 'camera', 'cocktail',
        'code', 'cog', 'drone', 'film', 'fish', 'gamepad', 'guitar', 'igloo',
        'joystick', 'lemon', 'lightbulb', 'location', 'medal', 'pencil', 'phone',
        'piano', 'robot', 'rocket', 'skiing', 'snake', 'squirrel', 'sun',
        'swords', 'tree-palm', 'turkey', 'unicorn', 'volcano', 'watch', 'wrench',
        'whistle',
    ];

    /**
     * Generate the URL that will return a random profile picture
     */
    public static function icon(): string
    {
        return 'fa-'.static::randomElement(static::$icons);
    }
}
