<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use Laravel\Cashier\Subscription;
use App\Nova\Actions\CancelSubscription;
use Laravel\Nova\Actions\DestructiveAction;
use App\Policies\Concerns\HandlesNovaAuthorization;

class SubscriptionPolicy
{
    use HandlesNovaAuthorization;

    public function viewAny(User $user): bool
    {
        return $this->hasSupportPermissions($user);
    }

    public function view(User $user): bool
    {
        return $this->hasSupportPermissions($user);
    }

    public function create(): bool
    {
        return false;
    }

    public function update(User $user): bool
    {
        return false;
    }

    public function delete(User $user): bool
    {
        return false;
    }

    public function runDestructiveAction(User $user, Subscription $subscription, DestructiveAction $action): bool
    {
        return $action instanceof CancelSubscription && $this->hasSupportPermissions($user);
    }
}
