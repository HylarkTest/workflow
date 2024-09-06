<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Http\Responses\Auth\FailedOneTimePasswordLoginResponse;

class OneTimePasswordLoginRequest extends OneTimePasswordRequest
{
    /**
     * Indicates if the user wished to be remembered after login.
     */
    protected bool $remember;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the user that is attempting the two factor challenge.
     */
    public function challengedUser(): User
    {
        if (isset($this->challengedUser)) {
            return $this->challengedUser;
        }

        if (! $this->session()->has('login.id')
            || ! $user = User::query()->find($this->session()->get('login.id'))) {
            throw new HttpResponseException(app(FailedOneTimePasswordLoginResponse::class)->toResponse($this));
        }
        /** @var \App\Models\User $user */

        return $this->challengedUser = $user;
    }

    /**
     * Determine if the user wanted to be remembered after login.
     */
    public function remember(): bool
    {
        if (! isset($this->remember)) {
            $this->remember = $this->session()->pull('login.remember', false);
        }

        return $this->remember;
    }

    /**
     * Determine if there is a challenged user.
     */
    public function hasChallengedUser(): bool
    {
        return $this->session()->has('login.id')
            && User::query()->find($this->session()->get('login.id'));
    }
}
