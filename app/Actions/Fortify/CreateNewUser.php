<?php

declare(strict_types=1);

namespace App\Actions\Fortify;

use App\Models\User;
use App\Models\Action;
use Actions\Core\ActionType;
use App\Models\MemberInvite;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @return \App\Models\User
     *
     * @throws ValidationException
     */
    public function create(array $input)
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                function ($attribute, $value, $fail) {
                    if (User::query()->where('email', ilike(), $value)->exists()) {
                        $fail(trans('validation.unique', ['email' => 'email']));
                    }
                },
            ],
            'password' => $this->passwordRules(),
            'permission' => 'accepted',
        ], [], ['name' => trans('validation.attributes.fullName')])->validate();

        $inviteId = session()->pull('member-invite');

        /** @var \App\Models\MemberInvite|null $invite */
        $invite = $inviteId ? MemberInvite::findOrFail($inviteId) : null;

        if ($invite && $invite->email !== $input['email']) {
            throw ValidationException::withMessages(['email' => ['The email address does not match the invite.']]);
        }

        $now = now();
        /** @var \App\Models\User $user */
        $user = User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => Hash::make($input['password']),
        ]);

        $personalBase = $user->firstPersonalBase();
        $personalBase->run(fn () => Action::createAction(
            $user,
            $personalBase->pivot,
            ActionType::CREATE(),
            null,
            $now // Make sure the event appears before base creation events
        ));

        if ($invite) {
            $user->acceptMemberInvite($invite);
            $user->finished_registration_at = now();
            $user->save();
            $user->setActiveBase($invite->base);
            // This usually happens in registration which is skipped here
            $personalBase->createDefaultEntries();
        }

        return $user;
    }
}
