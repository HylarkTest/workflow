<?php

declare(strict_types=1);

namespace App\Actions\Fortify;

use App\Models\User;
use App\Events\Auth\PasswordUpdated;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\UpdatesUserPasswords;

class UpdateUserPassword implements UpdatesUserPasswords
{
    use PasswordValidationRules;

    /**
     * Validate and update the user's password.
     *
     * @param  \App\Models\User  $user
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update($user, array $input): void
    {
        Validator::make($input, [
            'currentPassword' => ['string'],
            'password' => $this->passwordRules(),
        ])->after(function ($validator) use ($user, $input) {
            if (! $this->currentPasswordMatches($user, $input)) {
                $validator->errors()->add('currentPassword', __('The provided password does not match your current password.'));
            }
        })->validateWithBag('updatePassword');

        $isFirstPassword = ($user->password === null);

        $user->forceFill([
            'password' => Hash::make($input['password']),
        ])->save();

        event(new PasswordUpdated($user, $isFirstPassword));
    }

    protected function currentPasswordMatches(User $user, array $input): bool
    {
        if (! isset($input['currentPassword'])) {
            return false;
        }

        return Hash::check($input['currentPassword'], $user->password);
    }
}
