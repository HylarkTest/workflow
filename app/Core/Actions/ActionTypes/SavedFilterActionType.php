<?php

declare(strict_types=1);

namespace App\Core\Actions\ActionTypes;

use BenSampo\Enum\Enum;
use Actions\Core\ActionType;

/**
 * Class MappingActionType
 *
 * @method static \Actions\Core\ActionType SAVED_FILTER_CREATE()
 * @method static \Actions\Core\ActionType SAVED_FILTER_UPDATE()
 * @method static \Actions\Core\ActionType SAVED_FILTER_DELETE()
 * @method static \Actions\Core\ActionType PRIVATE_SAVED_FILTER_CREATE()
 * @method static \Actions\Core\ActionType PRIVATE_SAVED_FILTER_UPDATE()
 * @method static \Actions\Core\ActionType PRIVATE_SAVED_FILTER_DELETE()
 *
 * @extends \BenSampo\Enum\Enum<string>
 */
class SavedFilterActionType extends Enum
{
    public const SAVED_FILTER_CREATE = 'SAVED_FILTER_CREATE';

    public const SAVED_FILTER_UPDATE = 'SAVED_FILTER_UPDATE';

    public const SAVED_FILTER_DELETE = 'SAVED_FILTER_DELETE';

    public const PRIVATE_SAVED_FILTER_CREATE = 'PRIVATE_SAVED_FILTER_CREATE';

    public const PRIVATE_SAVED_FILTER_UPDATE = 'PRIVATE_SAVED_FILTER_UPDATE';

    public const PRIVATE_SAVED_FILTER_DELETE = 'PRIVATE_SAVED_FILTER_DELETE';

    public static function __callStatic(string $method, array $parameters): mixed
    {
        return ActionType::fromKey($method);
    }
}
