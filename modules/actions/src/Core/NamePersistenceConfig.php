<?php

declare(strict_types=1);

namespace Actions\Core;

enum NamePersistenceConfig: string
{
    public function isDeleteOrSoftDelete(): bool
    {
        return $this === self::ON_DELETE
            || $this === self::ON_SOFT_DELETE;
    }

    public function isOnDelete(): bool
    {
        return $this === self::ON_DELETE;
    }

    public function isOnSoftDelete(): bool
    {
        return $this === self::ON_SOFT_DELETE;
    }

    public function isOnUpdate(): bool
    {
        return $this === self::ON_UPDATE;
    }

    public function isAlways(): bool
    {
        return $this === self::ALWAYS;
    }

    public function isNever(): bool
    {
        return $this === self::NEVER;
    }
    case ALWAYS = 'ALWAYS';
    case NEVER = 'NEVER';
    case ON_UPDATE = 'ON_UPDATE';
    case ON_DELETE = 'ON_DELETE';
    case ON_SOFT_DELETE = 'ON_SOFT_DELETE';
}
