<?php

declare(strict_types=1);

namespace App\Policies\Concerns;

use App\Models\User;

trait HandlesNovaAuthorization
{
    public function hasEditorPermissions(User $user): bool
    {
        return $user->hasKnowledgeBaseRole();
    }

    public function hasManagerPermissions(User $user): bool
    {
        return $user->hasManagerRole();
    }

    public function hasSupportPermissions(User $user): bool
    {
        return $user->hasSupportRole();
    }
}
