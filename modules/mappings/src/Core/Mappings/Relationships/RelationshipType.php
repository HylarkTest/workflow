<?php

declare(strict_types=1);

namespace Mappings\Core\Mappings\Relationships;

use LaravelUtils\Enums\AdvancedEnum;

enum RelationshipType: string
{
    use AdvancedEnum;

    /**
     * @return static
     */
    public function inverse(): self
    {
        if ($this === self::ONE_TO_MANY) {
            return self::MANY_TO_ONE;
        }
        if ($this === self::MANY_TO_ONE) {
            return self::ONE_TO_MANY;
        }

        return $this;
    }

    public function isToMany(): bool
    {
        return $this === self::ONE_TO_MANY || $this === self::MANY_TO_MANY;
    }

    public function isToOne(): bool
    {
        return $this === self::ONE_TO_ONE || $this === self::MANY_TO_ONE;
    }

    case ONE_TO_ONE = 'ONE_TO_ONE';
    case ONE_TO_MANY = 'ONE_TO_MANY';
    case MANY_TO_ONE = 'MANY_TO_ONE';
    case MANY_TO_MANY = 'MANY_TO_MANY';
}
