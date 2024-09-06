<?php

declare(strict_types=1);

namespace App\Core\Preferences;

enum ThousandsSeparator: string
{
    case COMMA = ',';
    case DOT = '.';
    case SPACE = ' ';
    case UNDERSCORE = '_';
    case NONE = '';
}
