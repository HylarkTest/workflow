<?php

declare(strict_types=1);

namespace App\GraphQL;

use App\Models\Base;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\BaseUserPivot;
use Illuminate\Contracts\Auth\Authenticatable;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class AppContext implements GraphQLContext
{
    public function __construct(protected User $user, protected Base $base) {}

    public function user(): User
    {
        return $this->user;
    }

    public function setUser(?Authenticatable $user): void
    {
        if ($user instanceof User) {
            $this->user = $user;
        }
    }

    public function request(): ?Request
    {
        return null;
    }

    public function base(): Base
    {
        return $this->base;
    }

    public function baseUser(): BaseUserPivot
    {
        $base = $this->base();
        $user = $this->user();
        if ($base->pivot instanceof BaseUserPivot && $base->pivot->user_id === $user->id) {
            return $base->pivot;
        }
        if ($user->pivot instanceof BaseUserPivot && $user->pivot->base_id === $base->id) {
            return $user->pivot;
        }
        if ($user->relationLoaded('bases') && $user->bases->contains('id', $base->id)) {
            /** @phpstan-ignore-next-line If this does not work something is very wrong */
            return $user->bases->find($base->id)->pivot;
        }
        if ($base->relationLoaded('members') && $base->members->contains('id', $user->id)) {
            /** @phpstan-ignore-next-line If this does not work something is very wrong */
            return $base->members->find($user->id)->pivot;
        }

        /** @phpstan-ignore-next-line If this does not work something is very wrong */
        return $user->bases()->find($base->id)->pivot;
    }
}
