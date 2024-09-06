<?php

declare(strict_types=1);

namespace App\Policies;

use App\Policies\Concerns\HandlesNovaAuthorization;

/**
 * @method bool viewAny(\App\Models\User $user)
 * @method bool view(\App\Models\User $user, \App\Models\GlobalNotification $model)
 * @method bool create(\App\Models\User $user)
 * @method bool update(\App\Models\User $user, \App\Models\GlobalNotification $model)
 * @method bool delete(\App\Models\User $user, \App\Models\GlobalNotification $model)
 */
class GlobalNotificationPolicy
{
    use HandlesNovaAuthorization;

    public function __call(string $name, array $arguments): bool
    {
        if (! \in_array($name, [
            'viewAny',
            'view',
            'create',
            'update',
            'delete',
        ], true)) {
            throw new \BadMethodCallException(sprintf('Method %s::%s does not exist.', static::class, $name));
        }

        return $this->hasSupportPermissions($arguments[0]);
    }
}
