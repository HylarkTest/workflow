<?php

declare(strict_types=1);

namespace App\Policies\Concerns;

use App\Models\Base;
use App\Models\User;
use App\Models\BaseUserPivot;

trait HandlesBaseAuthorization
{
    protected function baseOrTenant(?Base $base = null): Base
    {
        return $base ?: tenant();
    }

    protected function isPersonalBase(?Base $base = null): bool
    {
        return $this->baseOrTenant($base)->isPersonal();
    }

    protected function getMemberModel(User $user, ?Base $base = null): ?BaseUserPivot
    {
        $base = $this->baseOrTenant($base);
        if ($base->pivot instanceof BaseUserPivot && $base->pivot->getAttribute('user_id') === $user->id) {
            return $base->pivot;
        }
        if ($user->pivot instanceof BaseUserPivot && $user->pivot->getAttribute('base_id') === $base->id) {
            return $user->pivot;
        }
        $baseId = $base->id;
        /** @var \App\Models\Base|null $base */
        $base = $user->bases->find($baseId);
        if ($base?->pivot->user_id !== $user->id) {
            /** @var \App\Models\Base|null $base */
            $base = $user->bases()->find($baseId);
        }

        return $base?->pivot;
    }

    protected function hasAdminPermissions(User $user, ?Base $base = null): bool
    {
        $member = $this->getMemberModel($user, $base);

        return $member?->role->hasAdminPermissions() ?: false;
    }

    protected function isOwner(User $user, ?Base $base = null): bool
    {
        $member = $this->getMemberModel($user, $this->baseOrTenant($base));

        return $member?->role->isOwner() ?: false;
    }
}
