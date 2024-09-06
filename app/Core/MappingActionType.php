<?php

declare(strict_types=1);

namespace App\Core;

use BenSampo\Enum\Enum;
use Actions\Core\ActionType;

/**
 * Class MappingActionType
 *
 * @method static \Actions\Core\ActionType CHANGE_MAPPING_DESIGN()
 * @method static \Actions\Core\ActionType ADD_MAPPING_FIELD()
 * @method static \Actions\Core\ActionType CHANGE_MAPPING_FIELD()
 * @method static \Actions\Core\ActionType REMOVE_MAPPING_FIELD()
 * @method static \Actions\Core\ActionType ADD_MAPPING_FEATURE()
 * @method static \Actions\Core\ActionType CHANGE_MAPPING_FEATURE()
 * @method static \Actions\Core\ActionType REMOVE_MAPPING_FEATURE()
 * @method static \Actions\Core\ActionType ADD_MAPPING_RELATIONSHIP()
 * @method static \Actions\Core\ActionType CHANGE_MAPPING_RELATIONSHIP()
 * @method static \Actions\Core\ActionType REMOVE_MAPPING_RELATIONSHIP()
 * @method static \Actions\Core\ActionType ADD_MAPPING_TAG_GROUP()
 * @method static \Actions\Core\ActionType CHANGE_MAPPING_TAG_GROUP()
 * @method static \Actions\Core\ActionType REMOVE_MAPPING_TAG_GROUP()
 *
 * @extends \BenSampo\Enum\Enum<string>
 */
class MappingActionType extends Enum
{
    public const CHANGE_MAPPING_DESIGN = 'CHANGE_MAPPING_DESIGN';

    public const ADD_MAPPING_FIELD = 'ADD_MAPPING_FIELD';

    public const CHANGE_MAPPING_FIELD = 'CHANGE_MAPPING_FIELD';

    public const REMOVE_MAPPING_FIELD = 'REMOVE_MAPPING_FIELD';

    public const ADD_MAPPING_FEATURE = 'ADD_MAPPING_FEATURE';

    public const CHANGE_MAPPING_FEATURE = 'CHANGE_MAPPING_FEATURE';

    public const REMOVE_MAPPING_FEATURE = 'REMOVE_MAPPING_FEATURE';

    public const ADD_MAPPING_RELATIONSHIP = 'ADD_MAPPING_RELATIONSHIP';

    public const CHANGE_MAPPING_RELATIONSHIP = 'CHANGE_MAPPING_RELATIONSHIP';

    public const REMOVE_MAPPING_RELATIONSHIP = 'REMOVE_MAPPING_RELATIONSHIP';

    public const ADD_MAPPING_TAG_GROUP = 'ADD_MAPPING_TAG_GROUP';

    public const CHANGE_MAPPING_TAG_GROUP = 'CHANGE_MAPPING_TAG_GROUP';

    public const REMOVE_MAPPING_TAG_GROUP = 'REMOVE_MAPPING_TAG_GROUP';

    public static function __callStatic(string $method, array $parameters): mixed
    {
        return ActionType::fromKey($method);
    }
}
