<?php

declare(strict_types=1);

namespace App\Core\Imports;

enum ImportItemStatus: string
{
    case IMPORTED = 'IMPORTED';
    case FAILED = 'FAILED';
    case REVERTED = 'REVERTED';
}
