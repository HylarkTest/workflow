<?php

declare(strict_types=1);

namespace Color;

enum ColorFormat: string
{
    case HEX = 'HEX';
    case RGB = 'RGB';
    case HSL = 'HSL';
}
