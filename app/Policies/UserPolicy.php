<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Policies\Concerns\HandlesNovaAuthorization;

/**
 * @method bool viewAny(\App\Models\User $user)
 * @method bool view(\App\Models\User $user, \App\Models\User $model)
 * @method bool create(\App\Models\User $user)
 * @method bool update(\App\Models\User $user, \App\Models\User $model)
 * @method bool delete(\App\Models\User $user, \App\Models\User $model)
 * @method bool restore(\App\Models\User $user, \App\Models\User $model)
 * @method bool forceDelete(\App\Models\User $user, \App\Models\User $model)
 * @method bool replicate(\App\Models\User $user, \App\Models\User $model)
 * @method bool updateAdminRole(\App\Models\User $user, \App\Models\User $model)
 */
class UserPolicy
{
    use HandlesNovaAuthorization;

    protected array $disabledActions = [
        'create',
        'replicate',
    ];

    protected array $basicActions = [
        'viewAny',
        'view',
        'update',
        'restore',
        'delete',
    ];

    protected array $managerActions = [
        'updateAdminRole',
        'forceDelete',
    ];

    public function hasBasicUserPermissions(User $user): bool
    {
        return $this->hasSupportPermissions($user)
            || $this->hasManagerPermissions($user);
    }

    public function viewNova(User $user): bool
    {
        if (! app()->environment('production')) {
            return true;
        }

        return $user->isAdmin() && $user->hasEnabledTwoFactorAuthentication();
    }

    /**
     * @param  array{0: \App\Models\User, 1?: \App\Models\User}  $arguments
     */
    public function __call(string $name, array $arguments): bool
    {
        if (isset($arguments[1]) && $arguments[0]->is($arguments[1])) {
            return true;
        }
        if (\in_array($name, $this->basicActions, true)) {
            return $this->hasBasicUserPermissions($arguments[0]);
        }
        if (\in_array($name, $this->managerActions, true)) {
            return $this->hasManagerPermissions($arguments[0]);
        }
        if (\in_array($name, $this->disabledActions, true)) {
            return false;
        }
        throw new \BadMethodCallException(sprintf('Method %s::%s does not exist.', static::class, $name));
    }
}
