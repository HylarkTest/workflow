<?php

declare(strict_types=1);

namespace App\Core\Account;

use BenSampo\Enum\FlaggedEnum;
use BenSampo\Enum\Attributes\Description;

/**
 * @method static static MANAGER()
 *                                 // @method static static CHANGE_REQUEST_AGENT()
 * @method static static KNOWLEDGE_BASE_AGENT()
 * @method static static SUPPORT()
 */
class AdminRole extends FlaggedEnum
{
    #[Description('Allowed to change and set Nova permissions')]
    public const MANAGER = 1 << 0;

    #[Description('Allowed to make changes to knowledge base resources')]
    public const KNOWLEDGE_BASE_AGENT = 1 << 1;

    #[Description('Allowed to see and change user info and subscriptions')]
    public const SUPPORT = 1 << 2;
    // #[Description("Allowed to make and approve change requests")]
    // public const CHANGE_REQUEST_AGENT = 1 << 3;

    public function isSuperAdmin(): bool
    {
        return $this->is(self::flags(self::getInstances()));
    }
}
