<?php

declare(strict_types=1);

namespace App\Core;

enum BaseType: string
{
    public function isPersonal(): bool
    {
        return $this === self::PERSONAL;
    }

    public function isCollaborative(): bool
    {
        return $this === self::COLLABORATIVE;
    }
    case PERSONAL = 'PERSONAL';
    case COLLABORATIVE = 'COLLABORATIVE';
}
