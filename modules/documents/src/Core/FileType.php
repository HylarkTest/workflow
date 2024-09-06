<?php

declare(strict_types=1);

namespace Documents\Core;

enum FileType: string
{
    case FILE = 'FILE';
    case DIRECTORY = 'DIRECTORY';
}
