<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use App\Models\User;

class VerifyOneTimePasswordRequest extends OneTimePasswordRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->hasChallengedUser();
    }

    /**
     * Get the user that is attempting the two factor challenge.
     */
    public function challengedUser(): User
    {
        /** @var User $user */
        $user = $this->user();

        return $user;
    }

    /**
     * Determine if there is a challenged user
     */
    public function hasChallengedUser(): bool
    {
        return $this->user() && $this->user()->hasOneTimePassword();
    }
}
