<?php

declare(strict_types=1);

namespace App\Core;

use BenSampo\Enum\Enum;
use Actions\Core\ActionType;

/**
 * Class MappingActionType
 *
 * @method static \Actions\Core\ActionType RELATIONSHIP_ADDED()
 * @method static \Actions\Core\ActionType RELATIONSHIP_REMOVED()
 *
 * @extends \BenSampo\Enum\Enum<string>
 */
class RelationshipActionType extends Enum
{
    public const RELATIONSHIP_ADDED = 'RELATIONSHIP_ADDED';

    public const RELATIONSHIP_REMOVED = 'RELATIONSHIP_REMOVED';

    public static function __callStatic(string $method, array $parameters): mixed
    {
        return ActionType::fromKey($method);
    }
}
