<?php

declare(strict_types=1);

namespace App\Core\Mappings;

enum MarkerFilterOperator: string
{
    case IS = 'IS';
    case IS_NOT = 'IS_NOT';
}
