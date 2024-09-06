<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Base;
use App\Models\User;
use App\Core\Groups\Role;
use App\Policies\Concerns\HandlesBaseAuthorization;

class BasePolicy
{
    use HandlesBaseAuthorization;

    public function viewInvites(User $user, ?Base $base = null): bool
    {
        return $this->hasAdminPermissions($user, $base);
    }

    public function access(User $user, Base $base): bool
    {
        return (bool) $this->getMemberModel($user, $base);
    }

    public function update(User $user, ?Base $base = null): bool
    {
        return $this->hasAdminPermissions($user, $base);
    }

    public function upgrade(User $user, ?Base $base = null): bool
    {
        return $this->isOwner($user, $base);
    }

    public function delete(User $user, ?Base $base = null): bool
    {
        if ($this->isPersonalBase($base)) {
            return false;
        }

        return $this->isOwner($user, $base);
    }

    public function leave(User $user, ?Base $base = null): bool
    {
        $base = $this->baseOrTenant($base);

        if ($this->isPersonalBase($base)) {
            return false;
        }

        // There must always be at least one owner on a base, so an owner can
        // only leave if there is another owner on the base.
        if ($this->isOwner($user, $base)) {
            return $base->members()
                ->wherePivot('role', Role::OWNER)
                ->whereKeyNot($user->id)
                ->exists();
        }

        return true;
    }

    public function invite(User $user, ?Base $base = null): bool
    {
        if (! $user->hasVerifiedEmail()) {
            return false;
        }

        return $this->hasAdminPermissions($user, $base);
    }

    public function addOwner(User $user, ?Base $base = null): bool
    {
        return $this->isOwner($user, $base);
    }
}
