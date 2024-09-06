<?php

declare(strict_types=1);

namespace App\Core;

use BenSampo\Enum\Enum;
use Actions\Core\ActionType;

/**
 * Class MappingActionType
 *
 * @method static \Actions\Core\ActionType MEMBER_INVITED()
 * @method static \Actions\Core\ActionType MEMBER_INVITE_RESENT()
 * @method static \Actions\Core\ActionType MEMBER_INVITE_ACCEPTED()
 * @method static \Actions\Core\ActionType MEMBER_ROLE_UPDATED()
 * @method static \Actions\Core\ActionType MEMBER_REMOVED()
 *
 * @extends \BenSampo\Enum\Enum<string>
 */
class MemberActionType extends Enum
{
    public const MEMBER_INVITED = 'MEMBER_INVITED';

    public const MEMBER_INVITE_RESENT = 'MEMBER_INVITE_RESENT';

    public const MEMBER_INVITE_ACCEPTED = 'MEMBER_INVITE_ACCEPTED';

    public const MEMBER_ROLE_UPDATED = 'MEMBER_ROLE_UPDATED';

    public const MEMBER_REMOVED = 'MEMBER_REMOVED';

    public static function __callStatic(string $method, array $parameters): mixed
    {
        return ActionType::fromKey($method);
    }
}
