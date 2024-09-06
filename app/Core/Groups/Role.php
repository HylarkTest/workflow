<?php

declare(strict_types=1);

namespace App\Core\Groups;

enum Role: string
{
    public function hasAdminPermissions(): bool
    {
        return $this === self::OWNER || $this === self::ADMIN;
    }

    public function isOwner(): bool
    {
        return $this === self::OWNER;
    }

    case OWNER = 'OWNER';
    case ADMIN = 'ADMIN';
    case MEMBER = 'MEMBER';
}
