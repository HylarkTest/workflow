<?php

declare(strict_types=1);

namespace App\Core;

use BenSampo\Enum\Enum;
use Actions\Core\ActionType;

/**
 * Class MappingActionType
 *
 * @method static \Actions\Core\ActionType COMPLETE()
 * @method static \Actions\Core\ActionType UNCOMPLETE()
 *
 * @extends \BenSampo\Enum\Enum<string>
 */
class TodoActionType extends Enum
{
    public const COMPLETE = 'COMPLETE';

    public const UNCOMPLETE = 'UNCOMPLETE';

    public static function __callStatic(string $method, array $parameters): mixed
    {
        return ActionType::fromKey($method);
    }
}
